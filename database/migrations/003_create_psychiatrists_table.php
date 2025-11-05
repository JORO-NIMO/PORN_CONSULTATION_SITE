<?php
return [
    'up' => [
        "CREATE TABLE IF NOT EXISTS psychiatrists (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            specialization VARCHAR(255),
            bio TEXT,
            experience_years INT DEFAULT 0,
            email VARCHAR(255),
            profile_image VARCHAR(255),
            rating DECIMAL(3,2) DEFAULT 0.00,
            total_consultations INT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        // Seed a few psychiatrists for demo purposes
        "INSERT IGNORE INTO psychiatrists (id, name, specialization, bio, experience_years, email, rating, is_active) VALUES
        (1, 'Dr. Sarah Mitchell', 'Addiction & Behavioral Health', 'Specialized in behavioral addictions including pornography addiction.', 12, 'dr.mitchell@example.com', 4.85, TRUE),
        (2, 'Dr. James Okello', 'Sexual Health & Relationships', 'Expert in sexual health counseling and relationship therapy.', 15, 'dr.okello@example.com', 4.92, TRUE)"
    ],
    'down' => "DROP TABLE IF EXISTS psychiatrists;"
];