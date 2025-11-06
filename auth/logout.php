<?php
require_once '../config/config.php';

if (isset($_SESSION['user_id'])) {
    $db = Database::getInstance();
    $db->query("DELETE FROM sessions WHERE user_id = ?", [$_SESSION['user_id']]);
}

// Clear session array
$_SESSION = [];

// Expire the session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

// Destroy the session and rotate the ID to prevent fixation
session_destroy();
@session_start();
session_regenerate_id(true);

header('Location: login.php');
exit;
