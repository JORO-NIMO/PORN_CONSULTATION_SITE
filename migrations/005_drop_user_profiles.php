<?php
/**
 * Migration 005: Drop user_profiles table
 */
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();

try {
    $db->beginTransaction();

    // Drop table if it exists
    $db->exec("DROP TABLE IF EXISTS user_profiles");

    $db->commit();
    echo "✅ Migration 005 applied successfully: user_profiles dropped\n";
} catch (Exception $e) {
    $db->rollBack();
    echo "❌ Migration 005 failed: " . $e->getMessage() . "\n";
    exit(1);
}
