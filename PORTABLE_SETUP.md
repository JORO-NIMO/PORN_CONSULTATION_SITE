# 🚀 Portable Setup Guide - No Database Installation Required!

The project has been modified to use **SQLite** - a file-based database that requires NO separate installation. The database is automatically created in the `data/` folder.

## ✅ What Changed

- **Database**: MySQL → SQLite (portable, file-based)
- **No Installation**: No need for MySQL, XAMPP, or any database server
- **Auto-Setup**: Database is created automatically on first run
- **Cross-Platform**: Works on Windows, Mac, Linux with just PHP

## 📋 Quick Start (3 Steps)

### Step 1: Install PHP (Portable Option)

**Option A: Download Portable PHP (No Installation)**
1. Download PHP 8.x from: https://windows.php.net/download/
2. Extract to any folder (e.g., `C:\php` or this project folder)
3. That's it! No installation needed.

**Option B: Install PHP (Traditional)**
1. Download PHP installer from: https://windows.php.net/download/
2. Run installer
3. Add PHP to PATH (installer option)

### Step 2: Verify PHP & SQLite

Open Command Prompt in this folder and run:
```bash
php -v
```

Check if SQLite is enabled:
```bash
php -m | findstr sqlite
```

If SQLite is not listed, edit `php.ini` and uncomment:
```ini
extension=sqlite3
extension=pdo_sqlite
```

### Step 3: Start the Server

**Windows:**
```bash
start-server.bat
```

**Mac/Linux:**
```bash
php -S localhost:8000
```

Then open: **http://localhost:8000**

## 📁 Project Structure

```
consultation_site/
├── data/
│   └── database.sqlite        # Auto-created SQLite database
├── config/
│   ├── database.php           # SQLite configuration
│   └── setup_sqlite.sql       # Database schema
├── start-server.bat           # Windows launcher
└── ... (other files)
```

## 🔧 Configuration

No configuration needed! The database is automatically created with:
- ✅ 4 sample psychiatrists
- ✅ 6 educational articles
- ✅ All tables and indexes

## 🌐 Accessing from Other Devices

To access from other devices on your network:

1. Find your IP address:
   ```bash
   ipconfig
   ```
   Look for "IPv4 Address" (e.g., 192.168.1.100)

2. Start server with your IP:
   ```bash
   php -S 0.0.0.0:8000
   ```

3. Access from other devices:
   ```
   http://YOUR_IP:8000
   ```
   Example: `http://192.168.1.100:8000`

## 📱 Mobile Access

1. Connect your phone to the same WiFi
2. Open browser on phone
3. Navigate to: `http://YOUR_COMPUTER_IP:8000`

## 🔒 Security Notes

- **Development Only**: Built-in PHP server is for development
- **Production**: Use Apache/Nginx for production deployment
- **Firewall**: Windows may ask to allow PHP - click "Allow"

## 🐛 Troubleshooting

### "PHP is not recognized"
- PHP is not installed or not in PATH
- Solution: Add PHP folder to Windows PATH or use portable PHP in project folder

### "SQLite extension not found"
- Edit `php.ini` (in PHP folder)
- Uncomment: `extension=sqlite3` and `extension=pdo_sqlite`
- Restart server

### "Permission denied" on data folder
- Run Command Prompt as Administrator
- Or manually create `data/` folder with write permissions

### Database not creating
- Check `data/` folder exists and is writable
- Delete `data/database.sqlite` and restart server (will recreate)

### Port 8000 already in use
- Change port: `php -S localhost:8080`
- Or stop other applications using port 8000

## 🎯 Features

All features work exactly the same:
- ✅ User registration & login
- ✅ Psychiatrist directory
- ✅ Anonymous messaging
- ✅ Video consultations
- ✅ Form builder
- ✅ Educational resources
- ✅ Progress tracking

## 📦 Portable Deployment

To share with others:
1. Zip the entire folder
2. Share the zip file
3. Recipient extracts and runs `start-server.bat`
4. No database setup needed!

## 🔄 Database Backup

Your database is a single file: `data/database.sqlite`

To backup:
```bash
copy data\database.sqlite data\database_backup.sqlite
```

To restore:
```bash
copy data\database_backup.sqlite data\database.sqlite
```

## 🚀 Production Deployment

For production, use:
- **Shared Hosting**: Upload files, SQLite works automatically
- **VPS**: Install PHP, upload files, configure web server
- **Cloud**: AWS/Google Cloud with PHP runtime

SQLite is perfect for:
- Small to medium traffic (< 100,000 requests/day)
- Single-server deployments
- Embedded applications

For high traffic, consider migrating to PostgreSQL/MySQL later.

## 📞 Support

If you encounter issues:
1. Check PHP version: `php -v` (need 7.4+)
2. Check SQLite: `php -m | findstr sqlite`
3. Check file permissions on `data/` folder
4. Review error messages in browser

---

**You're all set! No database installation, no complex setup. Just PHP and go! 🎉**
