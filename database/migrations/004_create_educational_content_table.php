<?php
return [
    'up' => [
        "CREATE TABLE IF NOT EXISTS educational_content (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            content_type ENUM('article','statistic','testimony','research') DEFAULT 'article',
            category VARCHAR(100),
            author VARCHAR(255),
            image_url VARCHAR(500),
            is_featured TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
    ],
    'down' => "DROP TABLE IF EXISTS educational_content;"
];