@echo off
echo ========================================
echo Mental Freedom Path - Portable Server Launcher
echo ========================================
echo.

REM Check if PHP is installed
where php >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] PHP is not installed or not in PATH
    echo.
    echo Please install PHP from: https://windows.php.net/download/
    echo Or use portable PHP: https://www.php.net/downloads
    echo.
    echo After installation, add PHP to your PATH or place php.exe in this folder
    pause
    exit /b 1
)

REM Display PHP version
echo Checking PHP installation...
php -v
echo.

REM Check if SQLite extension is enabled
php -m | findstr /i "sqlite" >nul
if %ERRORLEVEL% NEQ 0 (
    echo [WARNING] SQLite extension may not be enabled
    echo The application will attempt to run anyway...
    echo.
)

REM Start the server
echo Starting server on http://localhost:8000
echo.
echo Press Ctrl+C to stop the server
echo ========================================
echo.

php -S localhost:8000 -t .

pause
