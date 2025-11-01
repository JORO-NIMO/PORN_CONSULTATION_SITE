<?php
// Configuration for the application

// Start output buffering to prevent headers already sent errors
if (session_status() === PHP_SESSION_NONE) {
    // Session configuration must come before any output
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    
    // Start the session
    session_start();
}

// Database configuration (guard to prevent redefinition when config/database.php is included)
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'mental_freedom_path');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
// IMPORTANT: Set your database password here if you have one
if (!defined('DB_PASS')) {
    define('DB_PASS', '1234'); // Change this to your actual database password
}

// Gemini API Configuration
define('GEMINI_API_KEY', 'AIzaSyBEkRcO-j_JVMwTFB9fX5Wvg7UMuoVeGyU');

// Application settings
define('SITE_NAME', 'Mental Freedom Path');
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
define('BASE_URL', $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/consultation_site/');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('UTC');

// Error handler function
function handleError($errno, $errstr, $errfile, $errline) {
    error_log("Error [$errno] $errstr in $errfile on line $errline");
    if (ini_get('display_errors')) {
        echo "<div style='color: red; padding: 10px; margin: 10px; border: 1px solid red;'>";
        echo "<strong>Error:</strong> [$errno] $errstr<br>";
        echo "<small>in $errfile on line $errline</small>";
        echo "</div>";
    }
    return true; // Don't execute PHP internal error handler
}

// Set error handler
set_error_handler('handleError');

// Exception handler
function handleException($exception) {
    error_log("Uncaught exception: " . $exception->getMessage());
    if (ini_get('display_errors')) {
        echo "<div style='color: red; padding: 10px; margin: 10px; border: 1px solid red;'>";
        echo "<h3>An error occurred</h3>";
        echo "<p><strong>Message:</strong> " . $exception->getMessage() . "</p>";
        echo "<p><strong>File:</strong> " . $exception->getFile() . " on line " . $exception->getLine() . "</p>";
        echo "<pre>" . $exception->getTraceAsString() . "</pre>";
        echo "</div>";
    }
}

// Set exception handler
set_exception_handler('handleException');

// Include this file at the top of your PHP files:
// require_once __DIR__ . '/config.php';
