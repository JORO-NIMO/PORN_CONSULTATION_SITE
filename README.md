# Mental Freedom Path — Wellness Platform

A comprehensive web platform designed to support individuals struggling with pornography addiction through professional psychiatric help, anonymous messaging, educational resources, and community support. Integrated with X (formerly Twitter) for real-time recovery content and community engagement.

## 🌟 Features

### Core Functionality
- **Global Sidebar Navigation**: Consistent, collapsible sidebar with saved user preference
- **User Authentication**: Secure registration and login with bcrypt hashing
- **Psychiatrist Directory**: Browse and connect with specialized mental health professionals
- **Anonymous Messaging**: Safe, confidential communication system
- **Video Consultations**: WebRTC-ready anonymous video calling
- **Educational Content**: Curated resources about recovery and mental wellness
- **Exercises**: Breathing, grounding, relaxation, stretching
- **Discussions**: Lightweight topics stored locally on device
- **Practitioners**: Sample helper directory
- **Search**: Google GET interface (no API key)
- **Content Scraping (optional)**: Web scraper for educational content from reputable sources

### Security Features
- Password hashing with bcrypt (cost factor 12)
- CSRF token protection
- XSS prevention through input sanitization
- SQL injection protection with prepared statements
- Secure session management: strict mode, cookies-only, HttpOnly + SameSite=Lax cookies (Secure on HTTPS)
- Session hardening: fingerprint (UA + partial IP), idle timeout, session ID rotation every 15 minutes
- Baseline headers: X-Content-Type-Options, X-Frame-Options, Referrer-Policy
- Rate limiting on login attempts
- Anonymous video calling (identity protection)

### User Experience
- Modern, responsive design
- Smooth animations and transitions
- Real-time form validation
- AJAX-powered interactions
- Animated educational slideshow
- Mobile-friendly interface
- High-quality graphics and UI

## What's New (Oct 2025)
- Global, adjustable sidebar on every page (header links removed). Preference persists per user.
- New pages added and styled with the shared CSS:
  - `exercises.php` (breathing, grounding, relaxation, stretching)
  - `discussions.php` (lightweight topics stored locally)
  - `practitioners.php` (sample helper directory)
- Uploads feature disabled; `uploads.php` now redirects to `dashboard.php` and the sidebar link was removed.
- Profile feature removed; `profile.php` deleted and navigation links removed.
- All pages reference the single shared stylesheet `assets/css/style.css` with consistent HTML skeleton.

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+
- **Database**: SQLite (portable, no installation needed!)
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Video Calling**: WebRTC (Daily.co compatible)
- **X (Twitter) API**: For fetching real-time recovery content
- **Architecture**: MVC-inspired structure
- **Security**: bcrypt, prepared statements, CSRF tokens

## 🔌 X (Twitter) Integration

The platform includes integration with X (formerly Twitter) to display real-time recovery-related content. Here's how it works:

### Features
- Fetches recovery-related tweets using the X API v2
- Displays content in a clean, responsive feed
- Updates automatically via scheduled tasks
- Supports media (images) in tweets
- Secure API key management

### Setup
1. **Get X API Credentials**:
   - Apply for a developer account at [developer.x.com](https://developer.x.com/)
   - Create a new Project and App
   - Generate API keys and access tokens

2. **Configure API Keys**:
   - Copy `config/twitter_api_keys.php` to `config/api_keys.php`
   - Update with your X API credentials

3. **Run Database Migration**:
   ```bash
   php migrations/001_create_twitter_tables.php
   ```

4. **Test the Integration**:
   ```bash
   php scraper/fetch-twitter.php
   ```
   Visit: `http://your-site.com/twitter-feed.php`

5. **Schedule Updates** (Windows Task Scheduler):
   - Program: `C:\xampp\php\php.exe`
   - Arguments: `E:\xammp\htdocs\consultation_site\scraper\fetch-twitter.php`
   - Start in: `E:\xampp\htdocs\consultation_site\scraper\`
   - Trigger: Daily at 2:00 AM

## 📋 Installation

### Prerequisites
- **PHP 7.4 or higher** (with SQLite extension)
- **That's it!** No database server needed!

### 🚀 Quick Start (2 Steps)

1. **Install PHP** (if not already installed)
   - Download from: https://windows.php.net/download/
   - Or use portable PHP (no installation needed)

2. **Run the Server**
   
   **Windows:**
   ```bash
   start-server.bat
   ```
   
   **Mac/Linux:**
   ```bash
   php -S localhost:8000
   ```

3. **Open Browser**
   Navigate to: `http://localhost:8000`

**That's it!** The database is automatically created on first run with sample data.

### 📱 Access from Other Devices

Start server with:
```bash
php -S 0.0.0.0:8000
```

Then access from any device on your network:
```
http://YOUR_IP:8000
```

### 📖 Detailed Setup

See **[PORTABLE_SETUP.md](PORTABLE_SETUP.md)** for:
- Portable PHP installation
- Mobile access setup
- Troubleshooting
- Database backup
- Production deployment

## 📁 Project Structure

```
consultation_site/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet (shared by all pages)
│   └── js/
│       ├── main.js             # Core JavaScript
│       ├── auth.js             # Authentication
│       ├── messages.js         # Messaging system
│       ├── form-builder.js     # Dynamic forms
│       ├── slideshow.js        # Educational slideshow
│       └── video-call.js       # WebRTC video calling
├── auth/
│   ├── login.php               # User login
│   ├── register.php            # User registration
│   └── logout.php              # Session termination
├── config/
│   ├── config.php              # App configuration
│   ├── database.php            # Database class
│   └── setup.sql               # Database schema
├── includes/
│   ├── header.php              # Site header
│   ├── sidebar.php             # Global sidebar navigation
│   └── footer.php              # Site footer
├── scraper/
│   └── content-scraper.php     # Web scraping tool
├── index.php                   # Landing page
├── dashboard.php               # User dashboard
├── psychiatrists.php           # Psychiatrist directory
├── messages.php                # Anonymous messaging
├── education.php               # Educational resources
├── search.php                  # Google search (no API key)
├── exercises.php               # Stress-relief exercises
├── discussions.php             # Lightweight topics (local device)
├── practitioners.php           # Sample helper directory
├── video-call.php              # Video consultation
├── forms.php                   # Deprecated (legacy)
├── uploads.php                 # Disabled (redirects to dashboard)
└── README.md                   # This file
```

## 🗄️ Database Schema

### SQLite Database (data/database.sqlite)

**Auto-created on first run with:**
- **users**: User accounts with authentication
- **psychiatrists**: Mental health professionals (4 pre-loaded)
- **messages**: Anonymous messaging system
- **consultations**: Scheduled appointments
- **video_sessions**: Video call records
- **form_templates**: Custom assessment forms
- **form_submissions**: Form responses
- **educational_content**: Resources and articles (6 pre-loaded)
- **sessions**: Active user sessions

**Backup**: Simply copy `data/database.sqlite` file

## 🔒 Security Best Practices

1. **Generate unique SITE_KEY** in `config/config.php`
2. **Enable HTTPS** in production
3. **Set secure session cookies**:
   ```php
   ini_set('session.cookie_secure', 1);
   ini_set('session.cookie_httponly', 1);
   ini_set('session.cookie_samesite', 'Strict');
   ```
4. **Protect data folder**: Add `.htaccess` to deny web access
5. **Regular backups** of `data/database.sqlite`
6. **Update PHP** regularly
7. **Set file permissions**: `chmod 644 data/database.sqlite`

## 🎨 Customization

### Branding
- Edit `SITE_NAME` in `config/config.php`
- Replace logo in `includes/header.php`
- Customize colors in `assets/css/style.css` (CSS variables)

### Email Notifications
Integrate email service (e.g., PHPMailer, SendGrid) in:
- User registration confirmation
- Consultation reminders
- Message notifications

### Video Calling
Configure Daily.co or similar WebRTC service:
1. Sign up at https://daily.co
2. Add API key to `config/config.php`
3. Update `assets/js/video-call.js` with API integration

## 📊 Content Management

### Adding Psychiatrists
Insert via SQL or create admin panel:
```sql
INSERT INTO psychiatrists (name, specialization, bio, qualifications, experience_years, email, phone, availability, is_active)
VALUES ('Dr. Name', 'Specialization', 'Bio...', 'Qualifications', 10, 'email@example.com', '+256700000000', '{"monday": "9:00-17:00"}', 1);
```

### Adding Educational Content
```sql
INSERT INTO educational_content (title, content, content_type, category, is_featured)
VALUES ('Title', 'Content...', 'article', 'Category', 1);
```

### Running Web Scraper
```bash
php scraper/content-scraper.php
```
Or schedule with cron:
```bash
0 2 * * * cd /path/to/site && php scraper/content-scraper.php
```

## 🚀 Deployment

### Production Checklist
- [ ] Disable error display: `ini_set('display_errors', 0);`
- [ ] Enable HTTPS
- [ ] Set secure session settings
- [ ] Configure database backups
- [ ] Set up monitoring (uptime, errors)
- [ ] Configure email service
- [ ] Test all features
- [ ] Set up CDN for assets (optional)
- [ ] Configure caching (Redis/Memcached)
- [ ] Set up log rotation

### Hosting Recommendations
- **Shared Hosting**: Bluehost, SiteGround (basic)
- **VPS**: DigitalOcean, Linode, Vultr (recommended)
- **Cloud**: AWS, Google Cloud, Azure (scalable)

## 🤝 Contributing

This is a sensitive mental health platform. Contributions should:
- Maintain user privacy and anonymity
- Follow security best practices
- Be compassionate and non-judgmental
- Include proper documentation

## 📝 License

This project is designed for educational and therapeutic purposes. Please use responsibly and ethically.

## 🆘 Support

For technical support or feature requests, contact the development team.

### Crisis Resources
If you or someone you know is in crisis:
- National Suicide Prevention Lifeline: 1-800-273-8255
- Crisis Text Line: Text HOME to 741741
- International Association for Suicide Prevention: https://www.iasp.info/resources/Crisis_Centres/

## 🙏 Acknowledgments

This platform is built to support individuals on their recovery journey. We acknowledge the courage it takes to seek help and commit to providing a safe, supportive environment.

---

**Remember**: Recovery is possible. You are not alone. 🌟
#   P O R N _ C O N S U L T A T I O N _ S I T E  
 