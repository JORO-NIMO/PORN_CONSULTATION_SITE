<?php
// CLI script to create therapist_directory database and tables
// Usage: php database/create_therapist_directory.php [host] [user] [pass]

ini_set('display_errors', 0);
error_reporting(0);

$host = $argv[1] ?? '127.0.0.1';
$user = $argv[2] ?? 'root';
$pass = $argv[3] ?? '';

try {
    $pdo = new PDO("mysql:host={$host};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "Connected to MySQL at {$host}\n";

    // Read SQL file
    $sqlFile = __DIR__ . '/therapist_directory.sql';
    if (!is_file($sqlFile)) {
        throw new RuntimeException("Schema file not found: {$sqlFile}");
    }
    $sql = file_get_contents($sqlFile);

    // Execute statements
    $pdo->exec($sql);
    echo "Database and tables ensured (therapist_directory).\n";

    echo "Done.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(1);
}