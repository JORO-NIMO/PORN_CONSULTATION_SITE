<?php
// Simple database setup script
echo "Starting database setup...\n";

// Database connection parameters from config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'mental_freedom_path');
define('DB_USER', 'root');
define('DB_PASS', '1234');

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database created or already exists.\n";
    
    // Connect to the specific database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create users table with a superset of columns required by the app
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        username VARCHAR(100) NULL,
        name VARCHAR(255) NULL,
        first_name VARCHAR(100) NULL,
        last_name VARCHAR(100) NULL,
        password VARCHAR(255) NULL,
        password_hash VARCHAR(255) NULL,
        role VARCHAR(20) NOT NULL DEFAULT 'user',
        status VARCHAR(20) NOT NULL DEFAULT 'active',
        is_anonymous BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL
    ) ENGINE=InnoDB");
    echo "Users table ensured.\n";

    // Ensure missing columns exist if the table already existed
    $ensureColumn = function($col, $definition) use ($pdo) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users' AND COLUMN_NAME = ?");
        $stmt->execute([DB_NAME, $col]);
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN $col $definition");
            echo "Added column users.$col\n";
        }
    };

    $ensureColumn('username', 'VARCHAR(100) NULL');
    $ensureColumn('password', 'VARCHAR(255) NULL');
    $ensureColumn('role', "VARCHAR(20) NOT NULL DEFAULT 'user'");
    $ensureColumn('status', "VARCHAR(20) NOT NULL DEFAULT 'active'");
    $ensureColumn('first_name', 'VARCHAR(100) NULL');
    $ensureColumn('last_name', 'VARCHAR(100) NULL');
    
    // Insert test users if none exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
        $userPass = password_hash('user123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (id, email, username, name, password, password_hash, role, status) VALUES 
            (1, 'admin@example.com', 'admin', 'Admin User', '$adminPass', '$adminPass', 'admin', 'active'),
            (2, 'user@example.com', 'user', 'Test User', '$userPass', '$userPass', 'user', 'active')");
        echo "Test users added.\n";
    } else {
        // Make sure we have users with IDs 1 and 2
        $stmt = $pdo->query("SELECT id FROM users WHERE id IN (1, 2)");
        $existingIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!in_array(1, $existingIds)) {
            $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
            $pdo->exec("INSERT INTO users (id, email, username, name, password, password_hash, role, status) VALUES 
                (1, 'admin@example.com', 'admin', 'Admin User', '$adminPass', '$adminPass', 'admin', 'active')");
            echo "Added user with ID 1.\n";
        }
        
        if (!in_array(2, $existingIds)) {
            $userPass = password_hash('user123', PASSWORD_DEFAULT);
            $pdo->exec("INSERT INTO users (id, email, username, name, password, password_hash, role, status) VALUES 
                (2, 'user@example.com', 'user', 'Test User', '$userPass', '$userPass', 'user', 'active')");
            echo "Added user with ID 2.\n";
        }
    }

    // Create psychiatrists table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS psychiatrists (
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
    ) ENGINE=InnoDB");
    echo "Psychiatrists table created.\n";
    
    // Insert test psychiatrist if none exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM psychiatrists");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO psychiatrists (id, name, specialization, bio, experience_years, email) VALUES 
            (1, 'Dr. JORONIMO AMANYA', 'Clinical Psychology', 'Experienced therapist', 10, 'joronimo@mfpu.ug'),
            (2, 'Dr. John Doe', 'Psychiatry', 'Specializes in addiction recovery', 8, 'john@example.com')");
        echo "Test psychiatrists added.\n";
    } else {
        // Make sure we have psychiatrists with IDs 1 and 2
        $stmt = $pdo->query("SELECT id FROM psychiatrists WHERE id IN (1, 2)");
        $existingIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!in_array(1, $existingIds)) {
            $pdo->exec("INSERT INTO psychiatrists (id, name, specialization, bio, experience_years, email) VALUES 
                (1, 'Dr. Jane Smith', 'Clinical Psychology', 'Experienced therapist', 10, 'jane@example.com')");
            echo "Added psychiatrist with ID 1.\n";
        }
        
        if (!in_array(2, $existingIds)) {
            $pdo->exec("INSERT INTO psychiatrists (id, name, specialization, bio, experience_years, email) VALUES 
                (2, 'Dr. John Doe', 'Psychiatry', 'Specializes in addiction recovery', 8, 'john@example.com')");
            echo "Added psychiatrist with ID 2.\n";
        }
    }
    
    // Create educational_content table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS educational_content (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        category VARCHAR(100),
        author VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    echo "Educational content table created.\n";
    
    // Insert test content if none exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM educational_content");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO educational_content (title, content, category, author) VALUES 
            ('Understanding Mental Health', 'Mental health is essential to overall wellbeing...', 'Education', 'Dr. Jane Smith'),
            ('Recovery Strategies', 'Effective recovery involves multiple approaches...', 'Recovery', 'Dr. John Doe')");
        echo "Test educational content added.\n";
    }
    
    // Drop consultations table if it exists (to avoid foreign key issues)
    $pdo->exec("DROP TABLE IF EXISTS consultations");
    echo "Dropped consultations table if it existed.\n";
    
    // Create consultations table
    $pdo->exec("CREATE TABLE consultations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        psychiatrist_id INT NOT NULL,
        scheduled_time DATETIME NOT NULL,
        duration_minutes INT DEFAULT 60,
        status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
        notes TEXT,
        video_room_id VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (psychiatrist_id) REFERENCES psychiatrists(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    echo "Consultations table created.\n";
    
    // Insert test consultations
    $pdo->exec("INSERT INTO consultations (user_id, psychiatrist_id, scheduled_time, status) VALUES 
        (1, 1, DATE_ADD(NOW(), INTERVAL 1 DAY), 'scheduled'),
        (2, 2, DATE_ADD(NOW(), INTERVAL -7 DAY), 'completed')");
    echo "Test consultations added.\n";
    
    echo "Database setup completed successfully!\n";
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}
?>
    // Create messages table if it doesn't exist
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
    ) ENGINE=InnoDB");
    echo "Messages table created.\n";

    // Create sessions table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS sessions (
        id VARCHAR(128) PRIMARY KEY,
        user_id INT NOT NULL,
        ip_address VARCHAR(45),
        user_agent VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    echo "Sessions table created.\n";

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
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (psychiatrist_id) REFERENCES psychiatrists(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    echo "Consultations table created.\n";

    // Create chat tables if not exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS chat_rooms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        is_public BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    $pdo->exec("CREATE TABLE IF NOT EXISTS chat_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        room_id INT NOT NULL,
        message TEXT NOT NULL,
        attachment_path VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (room_id) REFERENCES chat_rooms(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    echo "Chat tables created.\n";