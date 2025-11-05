<?php
/**
 * TherapyRoute Scraper (CLI)
 * Usage examples:
 *   php scraper/therapyroute_scraper.php --country=Uganda --city=Kampala --pages=3 --delay=2-5
 *   php scraper/therapyroute_scraper.php --resume
 *
 * Notes:
 * - Respects robots and server load by delaying 1–5s (configurable) per request
 * - Stores progress in data/therapyroute_progress.json to resume
 * - Inserts/updates records in MySQL database `therapist_directory`
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/therapist_db.php';
require_once __DIR__ . '/../includes/mail_helper.php';

// -------- Config & Args --------
$args = getopt('', ['country::', 'city::', 'pages::', 'delay::', 'resume::', 'seed::', 'use-node::']);
$country = $args['country'] ?? '';
$city = $args['city'] ?? '';
$pages = (int)($args['pages'] ?? 1);
$delayRange = $args['delay'] ?? '1-5'; // "min-max"
$resume = array_key_exists('resume', $args);
// Seed mode: scrape profile URLs from data/uganda_seeds.txt directly
$seedMode = array_key_exists('seed', $args);
// Use Node headless browser to fetch listing links
$useNode = array_key_exists('use-node', $args);

[$delayMin, $delayMax] = (function($r){
    if (preg_match('/^(\d+)-(\d+)$/', $r, $m)) return [max(1,(int)$m[1]), max((int)$m[1],(int)$m[2])];
    return [1,5];
})($delayRange);

$progressFile = __DIR__ . '/../data/therapyroute_progress.json';
$seedFile = __DIR__ . '/../data/uganda_seeds.txt';
$progress = [
    'last_page' => 0,
    'processed_urls' => []
];
if ($resume && is_file($progressFile)) {
    $progress = json_decode(file_get_contents($progressFile), true) ?: $progress;
}

// Base listing URL format (subject to change per site structure)
$baseListUrl = 'https://www.therapyroute.com/search';

// -------- Helpers --------
function logLine(string $msg): void { echo '['.date('Y-m-d H:i:s')."] $msg\n"; }
function notifyAdmin(string $subject, string $html): void {
    if (defined('ADMIN_EMAIL') && filter_var(ADMIN_EMAIL, FILTER_VALIDATE_EMAIL)) {
        try { @send_mail_safe(ADMIN_EMAIL, $subject, $html); } catch (Throwable $e) { error_log('Scraper notify mail error: ' . $e->getMessage()); }
    }
}

function randDelay(int $min, int $max): void {
    $seconds = random_int($min, $max);
    logLine("Sleeping {$seconds}s to respect server load...");
    sleep($seconds);
}

function fetchURL(string $url): array {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (TherapistDirectoryBot; contact: admin@example.com)',
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HEADER => true,
        CURLOPT_ENCODING => '', // handle gzip/deflate
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
            'Referer: https://www.therapyroute.com/',
        ],
    ]);
    $response = curl_exec($ch);
    if ($response === false) {
        $err = curl_error($ch);
        $code = curl_errno($ch);
        curl_close($ch);
        return ['ok' => false, 'error' => "cURL error {$code}: {$err}", 'status' => 0, 'body' => ''];
    }
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    return ['ok' => $status >= 200 && $status < 300, 'status' => $status, 'headers' => $headers, 'body' => $body];
}

// Headless (Node) listing fetch: returns profile URLs for a given country and page
function headlessFetchProfileLinks(string $country, int $page): array {
    $script = __DIR__ . '/node/fetch_therapyroute_uganda.js';
    if (!is_file($script)) {
        logLine('Node headless script not found: ' . $script);
        return [];
    }
    $cmd = 'node ' . escapeshellarg($script) . ' --country=' . escapeshellarg($country) . ' --page=' . escapeshellarg((string)$page);
    $output = [];
    $exitCode = 0;
    exec($cmd . ' 2>&1', $output, $exitCode);
    if ($exitCode !== 0) {
        logLine('Headless fetch failed (exit ' . $exitCode . '): ' . implode(" | ", $output));
        notifyAdmin('[Scraper] Headless fetch failed', '<p>Exit code: ' . $exitCode . '</p><pre>' . htmlspecialchars(implode("\n", $output)) . '</pre>');
        return [];
    }
    $urls = [];
    foreach ($output as $line) {
        $line = trim($line);
        if ($line === '' || stripos($line, 'http') !== 0) continue;
        $path = parse_url($line, PHP_URL_PATH) ?? '';
        if ($path && preg_match('#^/therapist/.+#', $path)) {
            $urls[$line] = true;
        }
    }
    return array_keys($urls);
}

// Read seeds file containing profile URL paths or absolute URLs
function readSeedUrls(string $file): array {
    if (!is_file($file)) return [];
    $lines = array_map('trim', file($file));
    $urls = [];
    foreach ($lines as $ln) {
        if ($ln === '') continue;
        if (stripos($ln, 'http') === 0) {
            $u = $ln;
        } else {
            $u = 'https://www.therapyroute.com' . (str_starts_with($ln, '/') ? $ln : ('/' . $ln));
        }
        $path = parse_url($u, PHP_URL_PATH) ?? '';
        if ($path && preg_match('#^/therapist/.+#', $path)) {
            $urls[$u] = true;
        }
    }
    return array_values(array_keys($urls));
}

function saveListingSnapshot(string $html, string $country, int $page, string $tag = 'search'): void {
    $dir = __DIR__ . '/../data/therapyroute_listings';
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    $file = sprintf('%s/%s_page_%d_%s.html', $dir, $country ?: 'all', $page, $tag);
    @file_put_contents($file, $html);
}

function extractText(?DOMNode $node): string {
    return trim($node ? preg_replace('/\s+/', ' ', $node->textContent) : '');
}

function parseListing(string $html): array {
    // Return array of profile URLs
    $doc = new DOMDocument();
    @$doc->loadHTML($html);
    $xp = new DOMXPath($doc);
    $links = [];

    // 1) Anchor tags with known profile path segments
    $anchorQuery = '//a[contains(@href, "/therapist/") or contains(@href, "/counsellor/") or contains(@href, "/psychologist/")]';
    foreach ($xp->query($anchorQuery) as $a) {
        $href = $a->getAttribute('href');
        if (!$href) continue;
        if (strpos($href, 'http') !== 0) {
            $href = 'https://www.therapyroute.com' . (str_starts_with($href, '/') ? $href : '/' . $href);
        }
        // Avoid base path 404 like "/therapist/" without slug
        $path = parse_url($href, PHP_URL_PATH) ?? '';
        if ($path && preg_match('#^/therapist/.+#', $path)) {
            $links[$href] = true;
        } elseif (preg_match('#/(counsellor|psychologist)/.+#', $path)) {
            $links[$href] = true;
        }
    }

    // 2) Elements with data-url attributes pointing to profiles
    foreach ($xp->query('//*[@data-url]') as $el) {
        $href = $el->getAttribute('data-url');
        if (!$href) continue;
        if (strpos($href, 'http') !== 0) {
            $href = 'https://www.therapyroute.com' . (str_starts_with($href, '/') ? $href : '/' . $href);
        }
        $path = parse_url($href, PHP_URL_PATH) ?? '';
        if ($path && preg_match('#^/therapist/.+#', $path)) {
            $links[$href] = true;
        }
    }

    // 3) Known listing containers -> anchor links inside
    $containerQuery = '//*[contains(@class, "card") or contains(@class, "result") or contains(@class, "listing") or contains(@class, "profile")]//a[@href]';
    foreach ($xp->query($containerQuery) as $a) {
        $href = $a->getAttribute('href');
        if (!$href) continue;
        if (strpos($href, 'http') !== 0) {
            $href = 'https://www.therapyroute.com' . (str_starts_with($href, '/') ? $href : '/' . $href);
        }
        $path = parse_url($href, PHP_URL_PATH) ?? '';
        if ($path && preg_match('#^/(therapist|counsellor|psychologist)/.+#', $path)) {
            $links[$href] = true;
        }
    }

    // 4) Scan script/style/text nodes for embedded profile URLs
    $bodyText = $doc->textContent ?? '';
    if ($bodyText) {
        if (preg_match_all('#https?://(?:www\.)?therapyroute\.com/therapist/[^\s"\']+#i', $bodyText, $m)) {
            foreach ($m[0] as $href) {
                // Normalize and filter
                $href = preg_replace('/[\.,;]+$/', '', $href);
                $path = parse_url($href, PHP_URL_PATH) ?? '';
                if ($path && preg_match('#^/therapist/.+#', $path)) {
                    $links[$href] = true;
                }
            }
        }
    }

    return array_keys($links);
}

function parseProfile(string $html, string $url): array {
    $doc = new DOMDocument();
    @$doc->loadHTML($html);
    $xp = new DOMXPath($doc);
    // Name
    $nameNode = $xp->query('//h1 | //h2[contains(@class, "name") or contains(@class, "profile-name")]')->item(0);
    $name = extractText($nameNode);
    // Title
    $titleNode = $xp->query('//*[contains(@class, "title") or contains(@class, "profession") or contains(@class, "designation")]')->item(0);
    $title = extractText($titleNode);
    // Specialties
    $specNode = $xp->query('//*[contains(text(), "Specialties")]/following::ul[1] | //*[contains(@class, "specialties")]')->item(0);
    $specialties = extractText($specNode);
    // City/Country
    $city = '';
    $country = '';
    $locNode = $xp->query('//*[contains(@class, "location") or contains(text(), "Location")]/following::*[1]')->item(0);
    $locText = extractText($locNode);
    if ($locText) {
        if (preg_match('/^(.*?),\s*(.*?)$/', $locText, $m)) { $city = trim($m[1]); $country = trim($m[2]); }
        else { $city = $locText; }
    }
    // Languages
    $langNode = $xp->query('//*[contains(text(), "Language") or contains(text(), "Languages")]/following::ul[1]')->item(0);
    $languages = extractText($langNode);
    // Contact info
    $emailNode = $xp->query('//a[starts-with(@href, "mailto:")]')->item(0);
    $phoneNode = $xp->query('//*[contains(@class, "phone") or contains(text(), "Phone")]/following::*[1]')->item(0);
    $email = $emailNode ? preg_replace('/^mailto:/', '', $emailNode->getAttribute('href')) : '';
    $phone = extractText($phoneNode);
    // Source id from URL slug
    $sourceId = '';
    if (preg_match('#/([^/]+)$#', parse_url($url, PHP_URL_PATH) ?? '', $m)) {
        $sourceId = $m[1];
    }
    return [
        'source' => 'therapyroute',
        'source_id' => $sourceId,
        'name' => $name,
        'title' => $title,
        'specialties' => $specialties,
        'city' => $city,
        'country' => $country,
        'languages' => $languages,
        'contact_email' => $email,
        'phone' => $phone,
        'profile_url' => $url,
        'profile_html' => $html,
    ];
}

// -------- Main --------
$db = new TherapistDB();
$pdo = $db->pdo();
try {
    $pdo->exec('CREATE TABLE IF NOT EXISTS therapists (
        id INT AUTO_INCREMENT PRIMARY KEY,
        source VARCHAR(50) NOT NULL,
        source_id VARCHAR(255) NOT NULL,
        name VARCHAR(255) DEFAULT NULL,
        title VARCHAR(255) DEFAULT NULL,
        specialties TEXT DEFAULT NULL,
        city VARCHAR(255) DEFAULT NULL,
        country VARCHAR(255) DEFAULT NULL,
        languages VARCHAR(255) DEFAULT NULL,
        contact_email VARCHAR(255) DEFAULT NULL,
        phone VARCHAR(255) DEFAULT NULL,
        profile_url TEXT,
        profile_html MEDIUMTEXT,
        verified TINYINT(1) DEFAULT 0,
        last_scraped DATETIME DEFAULT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_source (source, source_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
    $pdo->exec('CREATE TABLE IF NOT EXISTS scrape_runs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        source VARCHAR(50) NOT NULL,
        started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        completed_at DATETIME DEFAULT NULL,
        new_count INT DEFAULT 0,
        updated_count INT DEFAULT 0,
        failed_count INT DEFAULT 0,
        notes TEXT DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
} catch (Throwable $e) {
    logLine('Failed to ensure tables: ' . $e->getMessage());
}

// Prepare statements
$upsert = $pdo->prepare('INSERT INTO therapists (
    source, source_id, name, title, specialties, city, country, languages, contact_email, phone, profile_url, profile_html, last_scraped
) VALUES (
    :source, :source_id, :name, :title, :specialties, :city, :country, :languages, :contact_email, :phone, :profile_url, :profile_html, NOW()
) ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    title = VALUES(title),
    specialties = VALUES(specialties),
    city = VALUES(city),
    country = VALUES(country),
    languages = VALUES(languages),
    contact_email = VALUES(contact_email),
    phone = VALUES(phone),
    profile_url = VALUES(profile_url),
    profile_html = VALUES(profile_html),
    last_scraped = NOW(),
    updated_at = NOW()');

$runId = null;
// Log run start
$pdo->exec("INSERT INTO scrape_runs (source) VALUES ('therapyroute')");
$runId = (int)$pdo->lastInsertId();
logLine("Scrape run #{$runId} started");

$new = 0; $updated = 0; $failed = 0;

for ($page = max(1, (int)$progress['last_page'] + 1); $page <= $pages; $page++) {
    $profileUrls = [];
    if ($seedMode) {
        // In seed mode, we don't process listing pages here
        $profileUrls = [];
    } else if ($useNode) {
        logLine('Headless listing fetch via Node for page ' . $page);
        $profileUrls = headlessFetchProfileLinks($country ?: 'Uganda', $page);
    } else {
        // Legacy static fetch (may yield 0 due to JS render)
        $qs = http_build_query(array_filter([
            'country' => $country ?: null,
            'city' => $city ?: null,
            'page' => $page,
        ]));
        $listUrl = $baseListUrl . ($qs ? ('?' . $qs) : '');
        logLine("Fetching listing page: {$listUrl}");
        $res = fetchURL($listUrl);
        if ($res['ok']) {
            saveListingSnapshot($res['body'], $country, $page, 'search');
            $profileUrls = parseListing($res['body']);
        } else {
            $failed++;
            logLine("Failed to fetch listing (HTTP {$res['status']}): {$listUrl}");
            notifyAdmin('[Scraper] Failed to fetch listing', '<p>HTTP status: ' . (int)$res['status'] . '</p><p>URL: ' . htmlspecialchars($listUrl) . '</p>');
        }
    }
    logLine("Found " . count($profileUrls) . " profile links on page {$page}");
    if (empty($profileUrls)) {
        notifyAdmin('[Scraper] No profile links found', '<p>Country: ' . htmlspecialchars($country ?: 'All') . '</p><p>Page: ' . (int)$page . '</p>');
    }

    foreach ($profileUrls as $purl) {
        if (isset($progress['processed_urls'][$purl])) { continue; }
        logLine("Fetching profile: {$purl}");
        $pRes = fetchURL($purl);
        if (!$pRes['ok']) { $failed++; logLine("Failed profile fetch (HTTP {$pRes['status']}): {$purl}"); randDelay($delayMin, $delayMax); continue; }

        $data = parseProfile($pRes['body'], $purl);
        // Basic sanitization
        foreach (['name','title','specialties','city','country','languages','contact_email','phone'] as $k) {
            if (isset($data[$k])) {
                $data[$k] = trim($data[$k]);
            }
        }

        try {
            $upsert->execute([
                ':source' => $data['source'],
                ':source_id' => $data['source_id'],
                ':name' => $data['name'],
                ':title' => $data['title'],
                ':specialties' => $data['specialties'],
                ':city' => $data['city'],
                ':country' => $data['country'],
                ':languages' => $data['languages'],
                ':contact_email' => $data['contact_email'],
                ':phone' => $data['phone'],
                ':profile_url' => $data['profile_url'],
                ':profile_html' => $data['profile_html'],
            ]);
            if ($upsert->rowCount() === 1) { $new++; } else { $updated++; }
            $progress['processed_urls'][$purl] = true;
        } catch (Throwable $e) {
            $failed++;
            logLine('DB upsert error: ' . $e->getMessage());
            notifyAdmin('[Scraper] DB upsert error', '<p>URL: ' . htmlspecialchars($purl) . '</p><pre>' . htmlspecialchars($e->getMessage()) . '</pre>');
        }

        randDelay($delayMin, $delayMax);
    }

    // Save progress
    $progress['last_page'] = $page;
    file_put_contents($progressFile, json_encode($progress, JSON_PRETTY_PRINT));
    randDelay($delayMin, $delayMax);
}

// Log run completion
$stmt = $pdo->prepare('UPDATE scrape_runs SET completed_at = NOW(), new_count = ?, updated_count = ?, failed_count = ?, notes = ? WHERE id = ?');
$stmt->execute([$new, $updated, $failed, 'Country=' . $country . '; City=' . $city . ($seedMode ? '; Mode=seed' : ($useNode ? '; Mode=node' : '')), $runId]);
logLine("Run #{$runId} completed. New={$new}, Updated={$updated}, Failed={$failed}");
notifyAdmin('[Scraper] Run completed', '<p>Run #' . (int)$runId . '</p><ul><li>New: ' . (int)$new . '</li><li>Updated: ' . (int)$updated . '</li><li>Failed: ' . (int)$failed . '</li></ul>');

// If seed mode was requested, process seed URLs after run init
if ($seedMode) {
    logLine('Seed mode enabled: reading ' . $seedFile);
    if (!is_file($seedFile)) {
        @mkdir(dirname($seedFile), 0777, true);
        @touch($seedFile);
    }
    $seedUrls = readSeedUrls($seedFile);
    logLine('Seed URLs found: ' . count($seedUrls));
    foreach ($seedUrls as $purl) {
        if (isset($progress['processed_urls'][$purl])) { continue; }
        logLine("Fetching profile (seed): {$purl}");
        $pRes = fetchURL($purl);
        if (!$pRes['ok']) { $failed++; logLine("Failed profile fetch (HTTP {$pRes['status']}): {$purl}"); randDelay($delayMin, $delayMax); continue; }

        $data = parseProfile($pRes['body'], $purl);
        foreach (['name','title','specialties','city','country','languages','contact_email','phone'] as $k) {
            if (isset($data[$k])) {
                $data[$k] = trim($data[$k]);
            }
        }
        try {
            $upsert->execute([
                ':source' => $data['source'],
                ':source_id' => $data['source_id'],
                ':name' => $data['name'],
                ':title' => $data['title'],
                ':specialties' => $data['specialties'],
                ':city' => $data['city'],
                ':country' => $data['country'],
                ':languages' => $data['languages'],
                ':contact_email' => $data['contact_email'],
                ':phone' => $data['phone'],
                ':profile_url' => $data['profile_url'],
                ':profile_html' => $data['profile_html'],
            ]);
            if ($upsert->rowCount() === 1) { $new++; } else { $updated++; }
            $progress['processed_urls'][$purl] = true;
        } catch (Throwable $e) {
            $failed++;
            logLine('DB upsert error: ' . $e->getMessage());
            notifyAdmin('[Scraper] DB upsert error (seed)', '<p>URL: ' . htmlspecialchars($purl) . '</p><pre>' . htmlspecialchars($e->getMessage()) . '</pre>');
        }
        randDelay($delayMin, $delayMax);
    }
    // Finalize run with seed mode note
    $stmt = $pdo->prepare('UPDATE scrape_runs SET completed_at = NOW(), new_count = ?, updated_count = ?, failed_count = ?, notes = ? WHERE id = ?');
    $stmt->execute([$new, $updated, $failed, 'Seed mode; Country=' . $country . '; City=' . $city, $runId]);
    logLine("Seed run #{$runId} completed. New={$new}, Updated={$updated}, Failed={$failed}");
    notifyAdmin('[Scraper] Seed run completed', '<p>Run #' . (int)$runId . '</p><ul><li>New: ' . (int)$new . '</li><li>Updated: ' . (int)$updated . '</li><li>Failed: ' . (int)$failed . '</li></ul>');
}