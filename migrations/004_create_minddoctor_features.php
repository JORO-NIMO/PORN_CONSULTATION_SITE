<?php
/**
 * Migration 004: Mind Doctor feature tables
 */
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();

try {
    $db->beginTransaction();

    // 1. User Profiles
    $db->exec("CREATE TABLE IF NOT EXISTS user_profiles (
        user_id INTEGER PRIMARY KEY,
        bio TEXT,
        avatar TEXT,
        preferences TEXT,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // 2. User Uploads
    $db->exec("CREATE TABLE IF NOT EXISTS user_uploads (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        title TEXT,
        description TEXT,
        category TEXT NOT NULL CHECK (category IN ('document','audio','video','research')),
        file_path TEXT NOT NULL,
        original_name TEXT,
        mime_type TEXT,
        file_size INTEGER,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_user_uploads_user ON user_uploads(user_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_user_uploads_category ON user_uploads(category)");

    // 3. Research Discussions
    $db->exec("CREATE TABLE IF NOT EXISTS research_discussions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        body TEXT NOT NULL,
        tags TEXT,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_discussions_user ON research_discussions(user_id)");

    $db->exec("CREATE TABLE IF NOT EXISTS research_replies (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        discussion_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        body TEXT NOT NULL,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (discussion_id) REFERENCES research_discussions(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_replies_discussion ON research_replies(discussion_id)");

    // 4. Wellness Search Logs
    $db->exec("CREATE TABLE IF NOT EXISTS wellness_search_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        query TEXT NOT NULL,
        results_count INTEGER DEFAULT 0,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_search_logs_user ON wellness_search_logs(user_id)");

    $db->commit();
    echo "✅ Migration 004 applied successfully\n";
} catch (Exception $e) {
    $db->rollBack();
    echo "❌ Migration 004 failed: " . $e->getMessage() . "\n";
    exit(1);
}
