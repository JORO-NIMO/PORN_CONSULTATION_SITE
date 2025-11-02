<?php
// Quick fix for the missing consultations table
require_once 'config.php';

try {
    // Connect to the database using the constants from config.php
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    
    // Create consultations table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS consultations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        psychiatrist_id INT NOT NULL,
        scheduled_time DATETIME NOT NULL,
        duration_minutes INT DEFAULT 60,
        status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
        notes TEXT,
        video_room_id VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    
    echo "Consultations table created successfully.\n";
    
    // Check if the table is empty and add sample data if needed
    $stmt = $pdo->query("SELECT COUNT(*) FROM consultations");
    if ($stmt->fetchColumn() == 0) {
        // Add some sample data
        $pdo->exec("INSERT INTO consultations (user_id, psychiatrist_id, scheduled_time, status) 
                   VALUES (1, 1, NOW(), 'scheduled'), (1, 2, DATE_ADD(NOW(), INTERVAL 1 DAY), 'scheduled')");
        echo "Sample consultation data added.\n";
    }
    
    echo "Fix completed successfully!\n";
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}
?>