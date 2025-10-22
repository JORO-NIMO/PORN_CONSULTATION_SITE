<?php
/**
 * Database migration for Twitter scraper
 * Run this once to create the required tables
 */

// Include database connection
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();

try {
    // Drop table if it exists
    $db->exec("DROP TABLE IF EXISTS scraped_twitter");
    
    // Create twitter_tweets table
    $db->exec("CREATE TABLE IF NOT EXISTS scraped_twitter (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tweet_id VARCHAR(50) NOT NULL UNIQUE,
        user_id VARCHAR(50) NOT NULL,
        user_name VARCHAR(100) NOT NULL,
        user_display_name VARCHAR(100) NOT NULL,
        user_avatar TEXT,
        text TEXT NOT NULL,
        media_url TEXT,
        url TEXT NOT NULL,
        created_at DATETIME NOT NULL,
        retweet_count INTEGER DEFAULT 0,
        favorite_count INTEGER DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Add indexes
    $db->exec("CREATE INDEX IF NOT EXISTS idx_twitter_user_id ON scraped_twitter(user_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_twitter_created_at ON scraped_twitter(created_at)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_twitter_is_active ON scraped_twitter(is_active)");

    echo "✅ Successfully created Twitter tables and indexes\n";
} catch (Exception $e) {
    die("❌ Error creating Twitter tables: " . $e->getMessage() . "\n");
}
