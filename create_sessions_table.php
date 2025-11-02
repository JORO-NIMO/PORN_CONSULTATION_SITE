<?php
// Include database configuration
require_once 'config/database.php';

try {
    // Create a new database instance
    $db = new Database();
    
    // Check if sessions table exists
    $result = $db->query("SHOW TABLES LIKE 'sessions'");
    if (count($result) === 0) {
        // Create sessions table
        $db->query("
            CREATE TABLE sessions (
                id VARCHAR(64) PRIMARY KEY,
                user_id INT NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                user_agent TEXT NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
        echo "Sessions table created successfully!";
    } else {
        echo "Sessions table already exists.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>