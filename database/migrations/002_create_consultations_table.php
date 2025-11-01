<?php
return [
    'up' => [
        "CREATE TABLE IF NOT EXISTS consultations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            therapist_id INT,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
            scheduled_for DATETIME NOT NULL,
            duration_minutes INT DEFAULT 60,
            meeting_link VARCHAR(512),
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
    ],
    'down' => "
        DROP TABLE IF EXISTS consultations;
    "
];
