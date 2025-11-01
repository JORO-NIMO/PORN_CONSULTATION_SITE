<?php
/**
 * Mental Freedom Path - Main Entry Point
 */

// Define application paths
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('VIEWS_PATH', APP_PATH . '/views');
define('CONFIG_PATH', ROOT_PATH . '/config');

// Start session
session_start();

// Load configuration
require_once CONFIG_PATH . '/config.php';

// Load Composer autoloader
require_once ROOT_PATH . '/vendor/autoload.php';

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
