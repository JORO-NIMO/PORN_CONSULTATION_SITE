<?php
return [
    'up' => [
        "CREATE TABLE IF NOT EXISTS messages (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
    ],
    'down' => "DROP TABLE IF EXISTS messages;"
];