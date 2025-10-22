-- Anti-Pornography Campaign Platform Database Schema (SQLite)

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT UNIQUE NOT NULL,
    name TEXT NOT NULL,
    password_hash TEXT NOT NULL,
    is_anonymous INTEGER DEFAULT 0,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    last_login TEXT
);

CREATE INDEX idx_users_email ON users(email);

-- Psychiatrists table
CREATE TABLE IF NOT EXISTS psychiatrists (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    specialization TEXT,
    bio TEXT,
    qualifications TEXT,
    experience_years INTEGER,
    email TEXT,
    phone TEXT,
    availability TEXT,
    profile_image TEXT,
    rating REAL DEFAULT 0.00,
    total_consultations INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_psychiatrists_active ON psychiatrists(is_active);

-- Form templates table
CREATE TABLE IF NOT EXISTS form_templates (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    created_by INTEGER,
    title TEXT NOT NULL,
    description TEXT,
    fields TEXT NOT NULL,
    share_token TEXT UNIQUE,
    is_public INTEGER DEFAULT 0,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_form_templates_token ON form_templates(share_token);

-- Form submissions table
CREATE TABLE IF NOT EXISTS form_submissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    form_id INTEGER NOT NULL,
    user_id INTEGER,
    data TEXT NOT NULL,
    submitted_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (form_id) REFERENCES form_templates(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_form_submissions_form ON form_submissions(form_id);

-- Messages table (anonymous messaging)
CREATE TABLE IF NOT EXISTS messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    sender_id INTEGER,
    recipient_id INTEGER,
    psychiatrist_id INTEGER,
    subject TEXT,
    message TEXT NOT NULL,
    is_anonymous INTEGER DEFAULT 1,
    is_read INTEGER DEFAULT 0,
    parent_id INTEGER,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (psychiatrist_id) REFERENCES psychiatrists(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_id) REFERENCES messages(id) ON DELETE CASCADE
);

CREATE INDEX idx_messages_sender ON messages(sender_id);
CREATE INDEX idx_messages_recipient ON messages(recipient_id);
CREATE INDEX idx_messages_psychiatrist ON messages(psychiatrist_id);

-- Consultations table
CREATE TABLE IF NOT EXISTS consultations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    psychiatrist_id INTEGER NOT NULL,
    scheduled_time TEXT NOT NULL,
    duration_minutes INTEGER DEFAULT 60,
    status TEXT DEFAULT 'scheduled' CHECK(status IN ('scheduled', 'in_progress', 'completed', 'cancelled')),
    notes TEXT,
    video_room_id TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (psychiatrist_id) REFERENCES psychiatrists(id) ON DELETE CASCADE
);

CREATE INDEX idx_consultations_user ON consultations(user_id);
CREATE INDEX idx_consultations_psychiatrist ON consultations(psychiatrist_id);
CREATE INDEX idx_consultations_status ON consultations(status);

-- Educational content table
CREATE TABLE IF NOT EXISTS educational_content (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    content TEXT NOT NULL,
    content_type TEXT DEFAULT 'article' CHECK(content_type IN ('article', 'statistic', 'testimony', 'research')),
    source_url TEXT,
    image_url TEXT,
    category TEXT,
    tags TEXT,
    views INTEGER DEFAULT 0,
    is_featured INTEGER DEFAULT 0,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_educational_content_type ON educational_content(content_type);
CREATE INDEX idx_educational_content_featured ON educational_content(is_featured);

-- Video call sessions table
CREATE TABLE IF NOT EXISTS video_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    consultation_id INTEGER,
    room_id TEXT UNIQUE NOT NULL,
    user_token TEXT NOT NULL,
    psychiatrist_token TEXT NOT NULL,
    started_at TEXT,
    ended_at TEXT,
    duration_seconds INTEGER DEFAULT 0,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (consultation_id) REFERENCES consultations(id) ON DELETE CASCADE
);

CREATE INDEX idx_video_sessions_room ON video_sessions(room_id);

-- Sessions table for user authentication
CREATE TABLE IF NOT EXISTS sessions (
    id TEXT PRIMARY KEY,
    user_id INTEGER NOT NULL,
    ip_address TEXT,
    user_agent TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    expires_at TEXT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_sessions_user ON sessions(user_id);
CREATE INDEX idx_sessions_expires ON sessions(expires_at);

-- Insert sample psychiatrists
INSERT INTO psychiatrists (name, specialization, bio, qualifications, experience_years, email, phone, availability, rating, is_active) VALUES
('Dr. Sarah Mitchell', 'Addiction & Behavioral Health', 'Specialized in treating behavioral addictions including pornography addiction. Compassionate, evidence-based approach.', 'PhD Clinical Psychology, Certified Addiction Specialist', 12, 'dr.mitchell@example.com', '+256700111222', '{"monday": "9:00-17:00", "wednesday": "9:00-17:00", "friday": "9:00-17:00"}', 4.85, 1),
('Dr. James Okello', 'Sexual Health & Relationships', 'Expert in sexual health counseling and relationship therapy. Helps individuals and couples overcome pornography-related issues.', 'MD Psychiatry, Sex Therapy Certification', 15, 'dr.okello@example.com', '+256700333444', '{"tuesday": "10:00-18:00", "thursday": "10:00-18:00", "saturday": "9:00-13:00"}', 4.92, 1),
('Dr. Emily Nakato', 'Cognitive Behavioral Therapy', 'Specializes in CBT for addiction recovery. Focuses on rewiring thought patterns and building healthy habits.', 'PhD Psychology, CBT Specialist', 8, 'dr.nakato@example.com', '+256700555666', '{"monday": "8:00-16:00", "tuesday": "8:00-16:00", "thursday": "8:00-16:00"}', 4.78, 1),
('Dr. Michael Ssempa', 'Family & Marriage Counseling', 'Helps families heal from the impact of pornography addiction. Restores trust and intimacy in relationships.', 'MA Family Therapy, Licensed Counselor', 10, 'dr.ssempa@example.com', '+256700777888', '{"wednesday": "11:00-19:00", "friday": "11:00-19:00"}', 4.88, 1);

-- Insert sample educational content
INSERT INTO educational_content (title, content, content_type, category, tags, is_featured) VALUES
('The Neuroscience of Pornography Addiction', 'Research shows that pornography consumption triggers the same brain pathways as substance addiction. The dopamine release creates a powerful reward cycle that becomes increasingly difficult to break. Studies indicate that regular users develop tolerance, requiring more extreme content for the same effect.', 'research', 'Science', '["neuroscience", "addiction", "brain"]', 1),
('Real Relationships vs. Digital Fantasy', 'Pornography creates unrealistic expectations about sex, bodies, and relationships. It can lead to decreased satisfaction in real intimate relationships, performance anxiety, and emotional disconnection from partners.', 'article', 'Relationships', '["relationships", "intimacy", "expectations"]', 1),
('Statistics: The Hidden Epidemic', '- 64% of young people actively seek out pornography weekly or more often
- 94% of children see pornography by age 14
- Pornography users are 3x more likely to experience erectile dysfunction
- 56% of divorces involve one party having an obsessive interest in pornographic websites', 'statistic', 'Statistics', '["statistics", "impact", "society"]', 1),
('Recovery is Possible: Success Stories', 'Thousands have successfully overcome pornography addiction. Recovery involves understanding triggers, building accountability, developing healthy coping mechanisms, and often professional support. The brain can heal and rewire itself with sustained abstinence.', 'testimony', 'Recovery', '["recovery", "hope", "success"]', 1),
('Impact on Mental Health', 'Pornography consumption is linked to increased rates of depression, anxiety, and low self-esteem. Users often experience shame, guilt, and isolation. The secretive nature of the habit compounds mental health challenges.', 'article', 'Mental Health', '["mental health", "depression", "anxiety"]', 0),
('Physical Effects and Sexual Dysfunction', 'Regular pornography use can lead to erectile dysfunction in young men, delayed ejaculation, and decreased sexual satisfaction. The overstimulation of the reward system affects real-world sexual response.', 'research', 'Health', '["physical health", "sexual dysfunction", "effects"]', 0);
