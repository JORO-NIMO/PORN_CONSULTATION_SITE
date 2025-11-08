<?php
/**
 * Mental Freedom Path - Main Entry Point
 */

// Define application paths
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('VIEWS_PATH', APP_PATH . '/views');
define('CONFIG_PATH', ROOT_PATH . '/config');

// Load configuration
require_once CONFIG_PATH . '/../config/config.php';

// Apply session settings before starting the session
ini_set('session.cookie_httponly', SESSION_COOKIE_HTTPONLY);
ini_set('session.use_only_cookies', SESSION_USE_ONLY_COOKIES);
ini_set('session.cookie_secure', SESSION_COOKIE_SECURE);
ini_set('session.cookie_samesite', SESSION_COOKIE_SAMESITE);

// Start session
session_start();

// Try to load Composer autoloader if it exists
$composerAutoload = ROOT_PATH . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

// Lightweight PSR-4 autoloader for the App namespace
spl_autoload_register(function($class) {
    $prefix = 'App\\';
    $baseDir = APP_PATH . '/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Initialize application
use App\Core\App;
use App\Core\Router;
use App\Core\Request;

// Initialize database connection
$db = new PDO(
    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
    DB_USER,
    DB_PASS
);

// Create router instance
$router = new Router();

// Load routes
require_once CONFIG_PATH . '/routes.php';

// Handle the request
$router->direct(Request::uri(), Request::method());
