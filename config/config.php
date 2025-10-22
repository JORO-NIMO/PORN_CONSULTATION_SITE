<?php
// Application configuration
session_start();

// Security settings
define('SITE_KEY', bin2hex(random_bytes(32))); // Generate unique key for CSRF
define('SESSION_LIFETIME', 3600 * 24); // 24 hours
define('BCRYPT_COST', 12);

// Site settings
define('SITE_NAME', 'Freedom Path - Pornography Recovery Support');
define('SITE_URL', 'http://localhost/consultation_site');
define('ADMIN_EMAIL', 'joronimoamanya@gmail.com');

// File upload settings
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Video call settings (using Daily.co or similar WebRTC service)
define('VIDEO_API_KEY', 'your_daily_co_api_key_here');
define('VIDEO_DOMAIN', 'your-domain.daily.co');

// Rate limiting
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Timezone
date_default_timezone_set('Africa/Kampala');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Auto-load database
require_once __DIR__ . '/database.php';

// Helper functions
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateToken();
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}
