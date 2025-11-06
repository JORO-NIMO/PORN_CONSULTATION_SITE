-- Anti-Pornography Campaign Platform Database Schema

CREATE DATABASE IF NOT EXISTS antiporn_campaign CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE antiporn_campaign;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_anonymous BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- Psychiatrists table
CREATE TABLE IF NOT EXISTS psychiatrists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    specialization VARCHAR(255),
    bio TEXT,
    qualifications TEXT,
    experience_years INT,
    email VARCHAR(255),
    phone VARCHAR(50),
    availability JSON,
    profile_image VARCHAR(255),
    rating DECIMAL(3,2) DEFAULT 0.00,
    total_consultations INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- Form templates table
CREATE TABLE IF NOT EXISTS form_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created_by INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    fields JSON NOT NULL,
    share_token VARCHAR(64) UNIQUE,
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_token (share_token)
) ENGINE=InnoDB;

-- Form submissions table
CREATE TABLE IF NOT EXISTS form_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    form_id INT NOT NULL,
    user_id INT,
    data JSON NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (form_id) REFERENCES form_templates(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_form (form_id)
) ENGINE=InnoDB;

-- Messages table (anonymous messaging)
CREATE TABLE IF NOT EXISTS messages (
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
    FOREIGN KEY (parent_id) REFERENCES messages(id) ON DELETE CASCADE,
    INDEX idx_sender (sender_id),
    INDEX idx_recipient (recipient_id),
    INDEX idx_psychiatrist (psychiatrist_id)
) ENGINE=InnoDB;

-- Consultations table
CREATE TABLE IF NOT EXISTS consultations (
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
    FOREIGN KEY (psychiatrist_id) REFERENCES psychiatrists(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_psychiatrist (psychiatrist_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Educational content table
CREATE TABLE IF NOT EXISTS educational_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    content_type ENUM('article', 'statistic', 'testimony', 'research') DEFAULT 'article',
    source_url VARCHAR(500),
    image_url VARCHAR(500),
    category VARCHAR(100),
    tags JSON,
    views INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (content_type),
    INDEX idx_featured (is_featured)
) ENGINE=InnoDB;

-- Video call sessions table
CREATE TABLE IF NOT EXISTS video_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    consultation_id INT,
    room_id VARCHAR(255) UNIQUE NOT NULL,
    user_token VARCHAR(255) NOT NULL,
    psychiatrist_token VARCHAR(255) NOT NULL,
    started_at TIMESTAMP NULL,
    ended_at TIMESTAMP NULL,
    duration_seconds INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (consultation_id) REFERENCES consultations(id) ON DELETE CASCADE,
    INDEX idx_room (room_id)
) ENGINE=InnoDB;

-- Sessions table for user authentication
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB;

-- User activities table for logging
CREATE TABLE IF NOT EXISTS user_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity VARCHAR(255) NOT NULL,
    status ENUM('success', 'failed', 'pending') DEFAULT 'success',
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_created (created_at),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Content categories table
CREATE TABLE IF NOT EXISTS content_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB;

-- Content table for articles and posts
CREATE TABLE IF NOT EXISTS content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content TEXT NOT NULL,
    excerpt TEXT,
    category_id INT,
    featured_image VARCHAR(500),
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    views INT DEFAULT 0,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES content_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_category (category_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Media library table
CREATE TABLE IF NOT EXISTS media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255),
    mime_type VARCHAR(100),
    size INT,
    path VARCHAR(500) NOT NULL,
    alt_text VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Insert sample psychiatrists
INSERT INTO psychiatrists (name, specialization, bio, qualifications, experience_years, email, phone, availability, rating, is_active) VALUES
('Dr. Sarah Mitchell', 'Addiction & Behavioral Health', 'Specialized in treating behavioral addictions including pornography addiction. Compassionate, evidence-based approach.', 'PhD Clinical Psychology, Certified Addiction Specialist', 12, 'dr.mitchell@example.com', '+256700111222', '{"monday": "9:00-17:00", "wednesday": "9:00-17:00", "friday": "9:00-17:00"}', 4.85, TRUE),
('Dr. James Okello', 'Sexual Health & Relationships', 'Expert in sexual health counseling and relationship therapy. Helps individuals and couples overcome pornography-related issues.', 'MD Psychiatry, Sex Therapy Certification', 15, 'dr.okello@example.com', '+256700333444', '{"tuesday": "10:00-18:00", "thursday": "10:00-18:00", "saturday": "9:00-13:00"}', 4.92, TRUE),
('Dr. Emily Nakato', 'Cognitive Behavioral Therapy', 'Specializes in CBT for addiction recovery. Focuses on rewiring thought patterns and building healthy habits.', 'PhD Psychology, CBT Specialist', 8, 'dr.nakato@example.com', '+256700555666', '{"monday": "8:00-16:00", "tuesday": "8:00-16:00", "thursday": "8:00-16:00"}', 4.78, TRUE),
('Dr. Michael Ssempa', 'Family & Marriage Counseling', 'Helps families heal from the impact of pornography addiction. Restores trust and intimacy in relationships.', 'MA Family Therapy, Licensed Counselor', 10, 'dr.ssempa@example.com', '+256700777888', '{"wednesday": "11:00-19:00", "friday": "11:00-19:00"}', 4.88, TRUE);

-- Insert sample educational content
INSERT INTO educational_content (title, content, content_type, category, tags, is_featured) VALUES
('The Neuroscience of Pornography Addiction', 'Research shows that pornography consumption triggers the same brain pathways as substance addiction. The dopamine release creates a powerful reward cycle that becomes increasingly difficult to break. Studies indicate that regular users develop tolerance, requiring more extreme content for the same effect.', 'research', 'Science', '["neuroscience", "addiction", "brain"]', TRUE),
('Real Relationships vs. Digital Fantasy', 'Pornography creates unrealistic expectations about sex, bodies, and relationships. It can lead to decreased satisfaction in real intimate relationships, performance anxiety, and emotional disconnection from partners.', 'article', 'Relationships', '["relationships", "intimacy", "expectations"]', TRUE),
('Statistics: The Hidden Epidemic', '- 64% of young people actively seek out pornography weekly or more often\n- 94% of children see pornography by age 14\n- Pornography users are 3x more likely to experience erectile dysfunction\n- 56% of divorces involve one party having an obsessive interest in pornographic websites', 'statistic', 'Statistics', '["statistics", "impact", "society"]', TRUE),
('Recovery is Possible: Success Stories', 'Thousands have successfully overcome pornography addiction. Recovery involves understanding triggers, building accountability, developing healthy coping mechanisms, and often professional support. The brain can heal and rewire itself with sustained abstinence.', 'testimony', 'Recovery', '["recovery", "hope", "success"]', TRUE),
('Impact on Mental Health', 'Pornography consumption is linked to increased rates of depression, anxiety, and low self-esteem. Users often experience shame, guilt, and isolation. The secretive nature of the habit compounds mental health challenges.', 'article', 'Mental Health', '["mental health", "depression", "anxiety"]', FALSE),
('Physical Effects and Sexual Dysfunction', 'Regular pornography use can lead to erectile dysfunction in young men, delayed ejaculation, and decreased sexual satisfaction. The overstimulation of the reward system affects real-world sexual response.', 'research', 'Health', '["physical health", "sexual dysfunction", "effects"]', FALSE);
