<?php
function isAdmin() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    global $db;
    $user = $db->selectOne('SELECT role FROM users WHERE id = ?', [$_SESSION['user_id']]);
    
    return $user && $user['role'] === 'admin';
}

function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['error'] = 'Access denied. Admin privileges required.';
        header('Location: /dashboard.php');
        exit();
    }
}

function redirectBasedOnRole() {
    if (isAdmin()) {
        header('Location: /admin/dashboard.php');
    } else {
        header('Location: /dashboard.php');
    }
    exit();
}
