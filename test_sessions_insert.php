<?php
// Quick verification: insert a session for the first user if available

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'mental_freedom_path');
define('DB_USER', 'root');
define('DB_PASS', '1234');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // Ensure sessions table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS sessions (
        id VARCHAR(64) PRIMARY KEY,
        user_id INT NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        user_agent TEXT NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // Find any user
    $user = $pdo->query("SELECT id, email, username FROM users ORDER BY id LIMIT 1")->fetch();
    if (!$user) {
        echo "No users found. Please register a user first.\n";
        exit(0);
    }

    // Insert a test session
    $sessionId = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare("INSERT INTO sessions (id, user_id, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))");
    $stmt->execute([$sessionId, $user['id'], '127.0.0.1', 'CLI-Test', 3600]);

    echo "Inserted test session for user #{$user['id']} ({$user['email']} / {$user['username']}).\n";
    echo "Session ID: {$sessionId}\n";

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>