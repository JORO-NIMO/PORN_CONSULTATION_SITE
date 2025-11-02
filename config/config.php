<?php
// Application configuration
if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? null) == 443);
    $cookieParams = [
        'lifetime' => 3600 * 24,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ];
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params($cookieParams);
    } else {
        session_set_cookie_params($cookieParams['lifetime'], $cookieParams['path'] . '; samesite=' . $cookieParams['samesite'], $cookieParams['domain'], $cookieParams['secure'], $cookieParams['httponly']);
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = time();
        $_SESSION['fingerprint'] = hash('sha256', ($_SERVER['HTTP_USER_AGENT'] ?? '') . '|' . substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 7));
    } else {
        $fp = hash('sha256', ($_SERVER['HTTP_USER_AGENT'] ?? '') . '|' . substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 7));
        if (!isset($_SESSION['fingerprint']) || $_SESSION['fingerprint'] !== $fp) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $p = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
            }
            session_destroy();
            header('Location: /auth/login.php?expired=1');
            exit;
        }
        // Periodic session ID rotation (every 15 minutes)
        if (!isset($_SESSION['last_regen']) || (time() - (int)$_SESSION['last_regen'] > 900)) {
            session_regenerate_id(true);
            $_SESSION['last_regen'] = time();
        }
    }
}

// Security settings
define('SITE_KEY', bin2hex(random_bytes(32))); // Generate unique key for CSRF
define('SESSION_LIFETIME', 3600 * 24); // 24 hours
define('BCRYPT_COST', 12);

// Site settings
define('SITE_NAME', 'Mental Freedom Path');
define('SITE_TAGLINE', 'Breaking the stigma to help parents raise resilient youth');
define('SITE_URL', 'http://localhost/consultation_site');
define('ADMIN_EMAIL', 'joronimoamanya@gmail.com');

// File upload settings
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('MAX_UPLOAD_SIZE_DOCUMENT', 20 * 1024 * 1024);
define('MAX_UPLOAD_SIZE_AUDIO', 50 * 1024 * 1024);
define('MAX_UPLOAD_SIZE_VIDEO', 200 * 1024 * 1024);

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

// Baseline security headers (safe defaults)
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// Auto-load database
require_once __DIR__ . '/database.php';

if (!defined('GOOGLE_API_KEY')) {
    define('GOOGLE_API_KEY', getenv('GOOGLE_API_KEY') ?: '');
}
if (!defined('GOOGLE_CX')) {
    define('GOOGLE_CX', getenv('GOOGLE_CX') ?: '');
}

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
        header('Location: /auth/login.php');
        exit;
    }
    if (isset($_SESSION['last_activity']) && (time() - (int)$_SESSION['last_activity'] > SESSION_LIFETIME)) {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        header('Location: /auth/login.php?expired=1');
        exit;
    }
    $_SESSION['last_activity'] = time();
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

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $db = Database::getInstance();
    try {
        return $db->fetchOne('SELECT * FROM users WHERE id = ?', [$_SESSION['user_id']]);
    } catch (Exception $e) {
        return null;
    }
}

function isAdmin($userId = null) {
    $userId = $userId ?? ($_SESSION['user_id'] ?? null);
    if (!$userId) return false;
    $db = Database::getInstance();
    try {
        $u = $db->fetchOne('SELECT email FROM users WHERE id = ?', [$userId]);
        return $u && isset($u['email']) && strtolower($u['email']) === strtolower(ADMIN_EMAIL);
    } catch (Exception $e) {
        return false;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /auth/login.php');
        exit;
    }
}

function getTotalUsers() {
    $db = Database::getInstance();
    try {
        $row = $db->fetchOne('SELECT COUNT(*) AS c FROM users');
        return (int)($row['c'] ?? 0);
    } catch (Exception $e) {
        return 0;
    }
}

function getActiveUsersToday() {
    $db = Database::getInstance();
    try {
        $row = $db->fetchOne("SELECT COUNT(*) AS c FROM users WHERE date(last_login) = date('now')");
        return (int)($row['c'] ?? 0);
    } catch (Exception $e) {
        return 0;
    }
}

function getNewContentCount($days = 7) {
    $db = Database::getInstance();
    try {
        $row = $db->fetchOne("SELECT COUNT(*) AS c FROM educational_content WHERE datetime(created_at) >= datetime('now', ?)", ['-' . (int)$days . ' days']);
        return (int)($row['c'] ?? 0);
    } catch (Exception $e) {
        return 0;
    }
}

function getPendingItemsCount() {
    $db = Database::getInstance();
    try {
        $row = $db->fetchOne('SELECT COUNT(*) AS c FROM news_articles WHERE is_approved = 0');
        return (int)($row['c'] ?? 0);
    } catch (Exception $e) {
        return 0;
    }
}

function getRecentActivities($limit = 10) {
    $db = Database::getInstance();
    try {
        return $db->fetchAll('SELECT * FROM user_activity ORDER BY created_at DESC LIMIT ?', [(int)$limit]);
    } catch (Exception $e) {
        return [];
    }
}

function getUnreadNotificationCount($userId) {
    return 0;
}

function getRecentNotifications($limit = 5) {
    return [];
}

function getUserFullName($userId) {
    $db = Database::getInstance();
    try {
        // Prefer full name, then username, then email
        $row = $db->fetchOne(
            'SELECT 
                COALESCE(
                    NULLIF(name, ''),
                    NULLIF(CONCAT_WS(" ", first_name, last_name), ''),
                    NULLIF(username, ''),
                    email
                ) AS display_name 
             FROM users WHERE id = ?',
            [$userId]
        );
        return $row['display_name'] ?? 'User';
    } catch (Exception $e) {
        return 'User';
    }
}

function timeAgo($datetime) {
    try {
        $ts = strtotime($datetime);
        $diff = time() - $ts;
        if ($diff < 60) return $diff . 's ago';
        if ($diff < 3600) return floor($diff/60) . 'm ago';
        if ($diff < 86400) return floor($diff/3600) . 'h ago';
        return floor($diff/86400) . 'd ago';
    } catch (Exception $e) {
        return $datetime;
    }
}

function formatDate($datetime) {
    try {
        return date('Y-m-d H:i', strtotime($datetime));
    } catch (Exception $e) {
        return (string)$datetime;
    }
}

function getStorageUsage() {
    $dir = __DIR__ . '/../data';
    $bytes = 0;
    if (is_dir($dir)) {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) { $bytes += $file->getSize(); }
    }
    $mb = $bytes / (1024*1024);
    $percent = min(100, (int)round(($mb / 1024) * 100));
    return $percent;
}

function getLastBackupTime() {
    return 'N/A';
}
