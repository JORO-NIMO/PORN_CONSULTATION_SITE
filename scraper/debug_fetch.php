<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$url = $argv[1] ?? ($_GET['url'] ?? '');
if (!$url) {
    fwrite(STDERR, "Usage: php scraper/debug_fetch.php <url>\n");
    exit(1);
}

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_HTTPHEADER => [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.9',
        'Referer: https://www.therapyroute.com/',
    ],
]);
$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

if ($response === false) {
    fwrite(STDERR, "cURL error: {$err}\n");
    exit(2);
}

$outDir = __DIR__ . '/../data';
if (!is_dir($outDir)) { @mkdir($outDir, 0777, true); }
$file = $outDir . '/debug_fetch.html';
file_put_contents($file, $response);

echo "HTTP {$status}\n";
echo "Saved to: {$file}\n";
echo "Length: " . strlen($response) . " bytes\n";
echo "Preview: \n";
echo substr($response, 0, 500) . "\n";
?>