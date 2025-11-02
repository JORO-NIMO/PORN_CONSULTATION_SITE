<?php
// Ensure messages table exists in MySQL
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

    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT,
        recipient_id INT,
        psychiatrist_id INT,
        subject VARCHAR(255),
        message TEXT NOT NULL,
        is_anonymous BOOLEAN DEFAULT TRUE,
        is_read BOOLEAN DEFAULT FALSE,
        parent_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (psychiatrist_id) REFERENCES psychiatrists(id) ON DELETE SET NULL,
        FOREIGN KEY (parent_id) REFERENCES messages(id) ON DELETE CASCADE
    )");

    echo "Messages table ensured.\n";

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>