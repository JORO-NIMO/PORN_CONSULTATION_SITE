<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth_helpers.php';

header('Content-Type: application/json');

// Check if user is admin
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    // Get total users
    $totalUsers = $db->selectValue('SELECT COUNT(*) FROM users');
    
    // Get active users (logged in last 30 days)
    $activeUsers = $db->selectValue(
        "SELECT COUNT(*) FROM users 
        WHERE last_login > DATE_SUB(NOW(), INTERVAL 30 DAY)"
    );
    
    // Get new users this month
    $newUsers = $db->selectValue(
        "SELECT COUNT(*) FROM users 
        WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)"
    );
    
    // Get active sessions
    $activeSessions = $db->selectValue(
        "SELECT COUNT(DISTINCT user_id) FROM user_sessions 
        WHERE last_activity > DATE_SUB(NOW(), INTERVAL 30 MINUTE)"
    );
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'data' => [
            'totalUsers' => (int)$totalUsers,
            'activeUsers' => (int)$activeUsers,
            'newUsers' => (int)$newUsers,
            'activeSessions' => (int)$activeSessions
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch dashboard stats',
        'debug' => DEBUG_MODE ? $e->getMessage() : null
    ]);
}
