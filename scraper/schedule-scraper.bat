@echo off
REM Windows Task Scheduler batch file to run scraper daily
REM Schedule this to run daily at a specific time

cd /d "%~dp0"
php advanced-scraper.php >> scraper.log 2>&1

REM Alternative: Run via web request
REM curl "http://localhost/consultation_site/scraper/advanced-scraper.php?run_advanced_scraper=1"
