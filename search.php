<?php
require_once __DIR__ . '/config/config.php';
$db = Database::getInstance();
$q = trim($_GET['q'] ?? '');
$results = [];
$error = '';
if ($q !== '' && GOOGLE_API_KEY && GOOGLE_CX) {
    $url = 'https://www.googleapis.com/customsearch/v1?key=' . urlencode(GOOGLE_API_KEY) . '&cx=' . urlencode(GOOGLE_CX) . '&q=' . urlencode($q) . '&num=10';
    $ctx = stream_context_create(['http' => ['timeout' => 8]]);
    $resp = @file_get_contents($url, false, $ctx);
    if ($resp !== false) {
        $data = json_decode($resp, true);
        if (isset($data['items'])) {
            foreach ($data['items'] as $it) {
                $results[] = [
                    'title' => $it['title'] ?? '',
                    'link' => $it['link'] ?? '#',
                    'snippet' => $it['snippet'] ?? '',
                    'displayLink' => $it['displayLink'] ?? ''
                ];
            }
        }
        $count = isset($data['searchInformation']['totalResults']) ? (int)$data['searchInformation']['totalResults'] : count($results);
        $db->query('INSERT INTO wellness_search_logs (user_id, query, results_count) VALUES (?, ?, ?)', [$_SESSION['user_id'] ?? null, $q, $count]);
    } else {
        $error = 'Could not fetch results at this time';
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Search Wellness — <?php echo SITE_NAME; ?></title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<main class="container" style="padding:2rem 1rem;">
    <h1>Search Better Ways of Living</h1>
    <form method="GET" action="https://www.google.com/search" target="_blank" style="margin:1rem 0; display:flex; gap:.5rem;">
        <input type="text" name="q" value="<?php echo sanitize($q); ?>" placeholder="Breathing exercises for stress, mindful routines, youth wellness" style="flex:1; padding:.75rem;">
        <input type="hidden" name="hl" value="en">
        <button class="btn btn-primary" type="submit">Search</button>
    </form>
    <?php if ($error): ?>
        <div class="alert error"><?php echo sanitize($error); ?></div>
    <?php endif; ?>
    <?php if ($q !== '' && empty($error) && GOOGLE_API_KEY && GOOGLE_CX): ?>
        <div style="margin:.5rem 0; color: var(--muted);">Results for "<?php echo sanitize($q); ?>"</div>
        <ul style="list-style:none; padding:0; display:grid; gap:1rem;">
            <?php foreach ($results as $r): ?>
                <li style="padding:1rem; border:1px solid #eee; border-radius:10px; background:#fff;">
                    <a href="<?php echo sanitize($r['link']); ?>" target="_blank" rel="noopener" style="font-weight:600; display:block; margin-bottom:.25rem;">
                        <?php echo sanitize($r['title']); ?>
                    </a>
                    <div style="font-size:.85rem; color:var(--muted); margin-bottom:.5rem;">
                        <?php echo sanitize($r['displayLink']); ?>
                    </div>
                    <div><?php echo sanitize($r['snippet']); ?></div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
