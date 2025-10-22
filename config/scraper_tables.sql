-- Database tables for scraped content

-- YouTube videos table
CREATE TABLE IF NOT EXISTS scraped_videos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    youtube_id VARCHAR(50) UNIQUE NOT NULL,
    title TEXT NOT NULL,
    description TEXT,
    thumbnail VARCHAR(500),
    channel VARCHAR(200),
    published_at DATETIME,
    source_type VARCHAR(50) DEFAULT 'youtube',
    is_featured BOOLEAN DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Research papers table
CREATE TABLE IF NOT EXISTS scraped_research (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    authors TEXT,
    journal VARCHAR(300),
    pubdate VARCHAR(50),
    pmid VARCHAR(50) UNIQUE,
    doi VARCHAR(100),
    url VARCHAR(500),
    abstract TEXT,
    source_type VARCHAR(50) DEFAULT 'pubmed',
    is_featured BOOLEAN DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Articles table
CREATE TABLE IF NOT EXISTS scraped_articles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    url VARCHAR(500) UNIQUE NOT NULL,
    excerpt TEXT,
    content TEXT,
    source VARCHAR(200),
    source_type VARCHAR(50) DEFAULT 'article',
    image_url VARCHAR(500),
    is_featured BOOLEAN DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Social media posts table
CREATE TABLE IF NOT EXISTS scraped_social (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    platform VARCHAR(50) NOT NULL, -- twitter, facebook, instagram
    post_id VARCHAR(100) UNIQUE NOT NULL,
    content TEXT NOT NULL,
    author VARCHAR(200),
    author_url VARCHAR(500),
    post_url VARCHAR(500),
    likes INTEGER DEFAULT 0,
    shares INTEGER DEFAULT 0,
    posted_at DATETIME,
    is_featured BOOLEAN DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Scraper logs table
CREATE TABLE IF NOT EXISTS scraper_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    scraper_type VARCHAR(50) NOT NULL,
    items_found INTEGER DEFAULT 0,
    items_saved INTEGER DEFAULT 0,
    status VARCHAR(50) DEFAULT 'success',
    error_message TEXT,
    run_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for better performance
CREATE INDEX IF NOT EXISTS idx_videos_youtube_id ON scraped_videos(youtube_id);
CREATE INDEX IF NOT EXISTS idx_videos_created ON scraped_videos(created_at DESC);
CREATE INDEX IF NOT EXISTS idx_research_pmid ON scraped_research(pmid);
CREATE INDEX IF NOT EXISTS idx_research_created ON scraped_research(created_at DESC);
CREATE INDEX IF NOT EXISTS idx_articles_url ON scraped_articles(url);
CREATE INDEX IF NOT EXISTS idx_articles_created ON scraped_articles(created_at DESC);
CREATE INDEX IF NOT EXISTS idx_social_platform ON scraped_social(platform);
CREATE INDEX IF NOT EXISTS idx_social_created ON scraped_social(created_at DESC);
