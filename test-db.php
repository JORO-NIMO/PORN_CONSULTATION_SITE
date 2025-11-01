<?php
// Test database connection
require_once 'config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
    
    echo "✅ Database connection successful!\n";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT DATABASE() as db");
    $result = $stmt->fetch();
    echo "✅ Connected to database: " . $result['db'] . "\n";
    
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage() . "\n");
}

// Test if required extensions are loaded
$required_extensions = ['pdo_mysql', 'curl', 'json'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    echo "\n❌ Missing required PHP extensions:\n";
    foreach ($missing_extensions as $ext) {
        echo "- $ext\n";
    }
    echo "\nPlease enable these extensions in your php.ini file.\n";
} else {
    echo "✅ All required PHP extensions are loaded.\n";
}

// Test file permissions
$directories = [
    'data',
    'assets/uploads',
    'cache'
];

echo "\nChecking directory permissions:\n";
foreach ($directories as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (!file_exists($path)) {
        @mkdir($path, 0755, true);
    }
    $writable = is_writable($path);
    echo ($writable ? '✅ ' : '❌ ') . "$dir is " . ($writable ? 'writable' : 'not writable') . "\n";
}
?>
