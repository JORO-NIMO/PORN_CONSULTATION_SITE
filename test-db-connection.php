<?php
// Test database connection with detailed error reporting
require_once 'config.php';

echo "<h2>Database Connection Test</h2>";
echo "<pre>";

try {
    // Test 1: Check if MySQL extension is loaded
    if (!extension_loaded('pdo_mysql')) {
        throw new Exception("PDO MySQL extension is not enabled. Please enable it in your php.ini file.");
    }
    echo "✅ PDO MySQL extension is loaded\n";

    // Test 2: Try to connect to MySQL server
    $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    echo "🔍 Attempting to connect to MySQL server...\n";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    echo "✅ Successfully connected to MySQL server\n";

    // Test 3: Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    $dbExists = $stmt->fetch();
    
    if (!$dbExists) {
        echo "⚠️ Database '" . DB_NAME . "' does not exist. Creating database...\n";
        try {
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "✅ Database created successfully\n";
        } catch (PDOException $e) {
            throw new Exception("Failed to create database: " . $e->getMessage());
        }
    } else {
        echo "✅ Database '" . DB_NAME . "' exists\n";
    }

    // Test 4: Connect to the specific database
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        $options
    );
    
    echo "✅ Successfully connected to database '" . DB_NAME . "'\n";

    // Test 5: Check if tables exist
    $requiredTables = ['users', 'sessions', 'messages'];
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\n📋 Checking for required tables...\n";
    foreach ($requiredTables as $table) {
        if (in_array($table, $tables)) {
            echo "✅ Table '$table' exists\n";
        } else {
            echo "⚠️ Table '$table' is missing\n";
        }
    }
    
    echo "\n🎉 Database connection test completed successfully!\n";

} catch (PDOException $e) {
    echo "\n❌ Database Error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
    
    // Provide troubleshooting tips based on error code
    switch ($e->getCode()) {
        case 2002:
            echo "\nTroubleshooting Tips:";
            echo "\n- Make sure MySQL server is running";
            echo "\n- Check if the hostname is correct (current: " . DB_HOST . ")";
            echo "\n- If using a custom port, add it to the host (e.g., 'localhost:3307')";
            break;
            
        case 1045:
            echo "\nTroubleshooting Tips:";
            echo "\n- Check your database username and password in config.php";
            echo "\n- Try logging in with these credentials using MySQL command line or phpMyAdmin";
            echo "\n- If you're using XAMPP, the default username is 'root' with no password";
            break;
            
        case 1049:
            echo "\nTroubleshooting Tips:";
            echo "\n- The database '" . DB_NAME . "' doesn't exist";
            echo "\n- Create the database using phpMyAdmin or run: CREATE DATABASE `" . DB_NAME . "`";
            break;
    }
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>
