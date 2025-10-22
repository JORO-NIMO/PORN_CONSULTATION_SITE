# Content Scraper System

## Overview
This system automatically fetches content from multiple sources daily:
- **YouTube**: Recovery testimonials and educational videos
- **PubMed/NCBI**: Research papers and scientific studies
- **Fight the New Drug**: Articles and educational content
- **X (formerly Twitter)**: Recovery community posts (optional)
- **Facebook/Instagram**: Social media content (optional)

## Setup Instructions

### 1. Install Required PHP Extensions
Ensure these PHP extensions are enabled in `php.ini`:
```
extension=curl
extension=dom
extension=json
```

### 2. Get API Keys

#### YouTube Data API v3 (Required for YouTube scraping)
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project
3. Enable "YouTube Data API v3"
4. Create credentials (API Key)
5. Add to environment variable: `YOUTUBE_API_KEY=your_key_here`

#### Twitter/X API (Optional)
1. Go to [Twitter Developer Portal](https://developer.twitter.com/)
2. Create a new app
3. Get Bearer Token
4. Add to environment variable: `TWITTER_BEARER_TOKEN=your_token_here`

#### Facebook Graph API (Optional)
1. Go to [Facebook Developers](https://developers.facebook.com/)
2. Create an app
3. Get Access Token
4. Add to environment variable: `FACEBOOK_ACCESS_TOKEN=your_token_here`

#### Instagram Basic Display API (Optional)
1. Go to [Instagram Developers](https://developers.facebook.com/docs/instagram-basic-display-api)
2. Create an app
3. Get Access Token
4. Add to environment variable: `INSTAGRAM_ACCESS_TOKEN=your_token_here`

### 3. Set Up Database Tables
Run the SQL script to create necessary tables:
```bash
php -r "require 'config/database.php'; $db = Database::getInstance(); $sql = file_get_contents('config/scraper_tables.sql'); $db->exec($sql);"
```

Or manually run the SQL in `config/scraper_tables.sql`

### 4. Test the Scraper
Run manually to test:
```bash
cd scraper
php advanced-scraper.php
```

Or via browser:
```
http://localhost/consultation_site/scraper/advanced-scraper.php?run_advanced_scraper=1
```

### 5. Schedule Daily Runs

#### Windows (Task Scheduler)
1. Open Task Scheduler
2. Create Basic Task
3. Name: "Content Scraper Daily"
4. Trigger: Daily at 2:00 AM
5. Action: Start a program
6. Program: `C:\xampp\php\php.exe`
7. Arguments: `E:\xammp\htdocs\consultation_site\scraper\advanced-scraper.php`
8. Start in: `E:\xammp\htdocs\consultation_site\scraper`

Or use the batch file:
1. Right-click `schedule-scraper.bat`
2. Create Task in Task Scheduler
3. Set to run daily

#### Linux/Mac (Cron Job)
Add to crontab:
```bash
0 2 * * * cd /path/to/consultation_site/scraper && php advanced-scraper.php >> scraper.log 2>&1
```

## Features

### X (Twitter) Scraper
- Fetches recovery-related posts from X
- Supports media (images) in posts
- Stores post metadata and engagement metrics
- Updates feed in real-time

### YouTube Scraper
- Searches for recovery testimonials
- Fetches video metadata (title, description, thumbnail)
- Stores video IDs for embedding
- Updates testimonials page automatically

### Research Paper Scraper
- Queries PubMed database
- Fetches latest research on pornography addiction
- Extracts titles, authors, journals, abstracts
- Links to original papers

### Article Scraper
- Scrapes educational content from trusted sources
- Extracts articles about addiction effects
- Stores full content for display

### Social Media Scraper (Optional)
- Monitors recovery hashtags
- Fetches community success stories
- Filters for relevant content

## Usage

### Manual Run
```bash
php advanced-scraper.php
```

### View Logs
Check `scraper.log` for execution history

### Database Queries
```sql
-- View latest YouTube videos
SELECT * FROM scraped_videos ORDER BY created_at DESC LIMIT 10;

-- View latest research papers
SELECT * FROM scraped_research ORDER BY created_at DESC LIMIT 10;

-- View scraper statistics
SELECT * FROM scraper_logs ORDER BY run_at DESC LIMIT 10;
```

## Updating Testimonials Page

The scraped YouTube videos can be automatically displayed on the testimonials page.

Edit `testimonials.php` to load from database:
```php
// Load from database instead of hardcoded array
$testimonials = $db->fetchAll(
    "SELECT * FROM scraped_videos WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6"
);
```

## Rate Limiting

The scraper includes built-in rate limiting:
- 1 second delay between API calls
- Respects API quotas
- Handles errors gracefully

## Troubleshooting

### "API key not configured"
- Set environment variables for API keys
- Or edit `advanced-scraper.php` and add keys directly

### "cURL error"
- Check internet connection
- Verify PHP cURL extension is enabled
- Check firewall settings

### "Database error"
- Run `scraper_tables.sql` to create tables
- Check database connection in `config.php`

### No content scraped
- Verify API keys are correct
- Check API quotas haven't been exceeded
- Review `scraper.log` for errors

## API Quotas

### YouTube Data API v3
- Free tier: 10,000 units/day
- Each search costs ~100 units
- ~100 searches per day possible

### PubMed API
- No API key required
- Rate limit: 3 requests/second
- No daily quota

### X API
- Free tier: Limited access
- Paid tier recommended for regular scraping
- API Documentation: https://developer.x.com/

## Best Practices

1. **Run during off-peak hours** (2-4 AM)
2. **Monitor API quotas** regularly
3. **Review scraped content** for quality
4. **Clean old content** periodically
5. **Backup database** before major updates

## Security Notes

- Never commit API keys to version control
- Use environment variables for sensitive data
- Restrict scraper access to authorized users only
- Validate all scraped content before display
- Sanitize HTML/JavaScript in scraped content

## Support

For issues or questions:
- Check logs: `scraper.log`
- Review database: `scraper_logs` table
- Contact: joronimoamanya@gmail.com
