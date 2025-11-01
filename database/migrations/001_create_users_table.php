<?php
return [
    'up' => [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            first_name VARCHAR(50),
            last_name VARCHAR(50),
            role ENUM('user', 'therapist', 'admin') DEFAULT 'user',
            profile_image VARCHAR(255),
            bio TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            last_login TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        // Insert default admin user (password: admin123)
        "INSERT IGNORE INTO users (username, email, password_hash, first_name, last_name, role, is_active) 
        VALUES (
            'admin', 
            'admin@example.com', 
            '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
            'Admin', 
            'User', 
            'admin', 
            TRUE
        )"
    ],
    'down' => "
        DROP TABLE IF EXISTS users;
    "
];
