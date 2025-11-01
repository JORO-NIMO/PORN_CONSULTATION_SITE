<?php
/**
 * Mental Freedom Path - Configuration
 */

// Environment settings
define('ENVIRONMENT', 'development'); // 'production' or 'development'

// Error reporting
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'mental_freedom_path');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application settings
define('APP_NAME', 'Mental Freedom Path');
define('APP_URL', 'http://localhost/mental-freedom-path');
define('APP_VERSION', '1.0.0');

// Security settings
define('HASH_KEY', 'your-secure-hash-key'); // Change this to a random string
define('ENCRYPTION_KEY', 'your-encryption-key'); // Change this to a random string
define('JWT_SECRET', 'your-jwt-secret'); // Change this to a random string

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.cookie_samesite', 'Lax');

// File upload settings
define('UPLOAD_PATH', dirname(__DIR__) . '/public/uploads');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['image/jpeg', 'image/png', 'application/pdf']);

// Email configuration
define('MAIL_HOST', 'smtp.example.com');
define('MAIL_USERNAME', 'noreply@example.com');
define('MAIL_PASSWORD', 'your-email-password');
define('MAIL_FROM', 'noreply@example.com');
define('MAIL_FROM_NAME', 'Mental Freedom Path');

// Emergency services
define('EMERGENCY_PHONE', '911');
define('CRISIS_HOTLINE', '1-800-273-8255'); // US National Suicide Prevention Lifeline

// Feature flags
define('FEATURE_AI_ASSISTANT', true);
define('FEATURE_VIDEO_CALLS', true);
define('FEATURE_GAMIFICATION', true);

// Timezone
date_default_timezone_set('UTC');

// Error handling
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $errorType = [
        E_ERROR => 'Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Strict Standards',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated',
    ][$errno] ?? 'Unknown Error';
    
    $message = sprintf(
        '%s: %s in %s on line %d',
        $errorType,
        $errstr,
        $errfile,
        $errline
    );
    
    // Log the error
    error_log($message);
    
    // In development, display the error
    if (ENVIRONMENT === 'development') {
        echo "<div style='color: #a00; padding: 10px; margin: 10px; border: 1px solid #f00;'>";
        echo "<strong>$message</strong>";
        echo "</div>";
    }
    
    return true;
});

// Exception handler
set_exception_handler(function($exception) {
    error_log("Uncaught exception: " . $exception->getMessage());
    
    if (ENVIRONMENT === 'development') {
        echo "<div style='color: #a00; padding: 10px; margin: 10px; border: 1px solid #f00;'>";
        echo "<h2>Uncaught Exception</h2>";
        echo "<p><strong>Message:</strong> " . $exception->getMessage() . "</p>";
        echo "<p><strong>File:</strong> " . $exception->getFile() . " on line " . $exception->getLine() . "</p>";
        echo "<pre>" . $exception->getTraceAsString() . "</pre>";
        echo "</div>";
    } else {
        // In production, show a generic error page
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }
        include VIEWS_PATH . '/errors/500.php';
    }
});
