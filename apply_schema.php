<?php
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance();
$pdo = $db->getPdo();

$sql = file_get_contents(__DIR__ . '/config/setup.sql');

if ($sql === false) {
    die("Error: Could not read SQL file.\n");
}

try {
    $pdo->exec($sql);
    echo "Database schema updated successfully!\n";
} catch (PDOException $e) {
    die("Error updating database schema: " . $e->getMessage() . "\n");
}
?>