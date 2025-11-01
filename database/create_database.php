<?php
// This script creates the MySQL database and user
// Run this script once to set up the database

// Database configuration
$db_host = 'localhost';
$db_root_user = 'root';  // Default XAMPP MySQL root user
$db_root_pass = '';      // Default XAMPP MySQL root password (empty by default)
$db_name = 'consultation_site';
$db_user = 'consultation_user';
$db_pass = 'secure_password_123'; // Change this to a strong password

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$db_host", $db_root_user, $db_root_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Create user and grant privileges
    $pdo->exec("CREATE USER IF NOT EXISTS '$db_user'@'localhost' IDENTIFIED BY '$db_pass'");
    $pdo->exec("GRANT ALL PRIVILEGES ON `$db_name`.* TO '$db_user'@'localhost'");
    $pdo->exec("FLUSH PRIVILEGES");
    
    echo "Database and user created successfully!\n";
    echo "Database: $db_name\n";
    echo "Username: $db_user\n";
    echo "Password: $db_pass\n";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}

// Now let's create the tables using migrations
require_once __DIR__ . '/migrate.php';

echo "\nDatabase setup completed!\n";
