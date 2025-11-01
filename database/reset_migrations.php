<?php
// Load configuration and database connection
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Get database instance
$db = Database::getInstance();

try {
    // Drop all tables
    $tables = $db->fetchAll("SHOW TABLES");
    $db->query('SET FOREIGN_KEY_CHECKS = 0');
    
    foreach ($tables as $table) {
        $tableName = array_values($table)[0];
        echo "Dropping table: $tableName\n";
        $db->query("DROP TABLE IF EXISTS `$tableName`");
    }
    
    $db->query('SET FOREIGN_KEY_CHECKS = 1');
    echo "\nAll tables dropped successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
