<?php
/**
 * Database migration for new features
 * - Chat system
 * - Content management
 * - News integration
 */

// Include database connection
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();

try {
    // Begin transaction
    $db->beginTransaction();

    // Ensure base schema exists (users, consultations, educational_content, etc.)
    // This will create base tables if they are missing. Safe to run once.
    $schemaSql = file_get_contents(__DIR__ . '/../config/setup_sqlite.sql');
    if ($schemaSql !== false) {
        $db->exec($schemaSql);
    }

    // 1. Chat Messages Table
    $db->exec("CREATE TABLE IF NOT EXISTS chat_messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        room_id INTEGER NOT NULL,
        message TEXT NOT NULL,
        attachment_path TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // 2. Chat Rooms Table
    $db->exec("CREATE TABLE IF NOT EXISTS chat_rooms (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        is_public BOOLEAN DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 3. Content Table
    $db->exec("CREATE TABLE IF NOT EXISTS content (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        content_type TEXT NOT NULL CHECK (content_type IN ('video','article','resource')),
        file_path TEXT,
        url TEXT,
        is_featured BOOLEAN DEFAULT 0,
        created_by INTEGER,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    )");

    // 4. Daily Topics Table
    $db->exec("CREATE TABLE IF NOT EXISTS daily_topics (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        scheduled_date DATE NOT NULL,
        is_published BOOLEAN DEFAULT 0,
        created_by INTEGER,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    )");

    // 5. Mental Health Resources Table
    $db->exec("CREATE TABLE IF NOT EXISTS mental_health_resources (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        resource_type TEXT NOT NULL CHECK (resource_type IN ('article','video','workshop','support_group')),
        content TEXT,
        file_path TEXT,
        url TEXT,
        is_featured BOOLEAN DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 6. News Articles Table
    $db->exec("CREATE TABLE IF NOT EXISTS news_articles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        source_id VARCHAR(100) NOT NULL,
        source_name VARCHAR(100) NOT NULL,
        author VARCHAR(255),
        title VARCHAR(255) NOT NULL,
        description TEXT,
        url TEXT NOT NULL,
        url_to_image TEXT,
        published_at TIMESTAMP,
        content TEXT,
        category VARCHAR(50) DEFAULT 'general',
        is_approved BOOLEAN DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(source_id, source_name)
    )");

    // 7. News Sources Table
    $db->exec("CREATE TABLE IF NOT EXISTS news_sources (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(100) NOT NULL,
        api_id VARCHAR(100) NOT NULL,
        api_key TEXT,
        is_active BOOLEAN DEFAULT 1,
        last_fetched TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(api_id)
    )");

    // 8. User Favorites
    $db->exec("CREATE TABLE IF NOT EXISTS user_favorites (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        content_type TEXT NOT NULL CHECK (content_type IN ('content','article','resource')),
        content_id INTEGER NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(user_id, content_type, content_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // 9. User Activity Log
    $db->exec("CREATE TABLE IF NOT EXISTS user_activity (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        activity_type VARCHAR(50) NOT NULL,
        details TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )");

    // 10. Create indexes for better performance
    $db->exec("CREATE INDEX IF NOT EXISTS idx_chat_messages_room ON chat_messages(room_id, created_at)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_content_type ON content(content_type, is_featured)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_daily_topics_date ON daily_topics(scheduled_date, is_published)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_news_articles_date ON news_articles(published_at, category, is_approved)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_user_favorites ON user_favorites(user_id, content_type)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_user_activity ON user_activity(user_id, activity_type, created_at)");

    // Commit transaction
    $db->commit();
    
    echo "✅ Database tables created successfully\n";
    
} catch (Exception $e) {
    // Rollback transaction on error
    $db->rollBack();
    echo "❌ Error creating tables: " . $e->getMessage() . "\n";
    exit(1);
}

// Add default data
require_once __DIR__ . '/../config/database.php';

try {
    // Insert default chat room
    $db->exec("INSERT OR IGNORE INTO chat_rooms (name, description, is_public) 
               VALUES ('General Support', 'General support and discussion', 1)");
    
    echo "✅ Default data inserted successfully\n";
    
} catch (Exception $e) {
    echo "⚠️ Warning: Could not insert default data: " . $e->getMessage() . "\n";
}

echo "🎉 Database migration completed successfully!\n";
