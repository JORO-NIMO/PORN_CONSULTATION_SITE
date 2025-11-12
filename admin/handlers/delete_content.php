<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/jwt_middleware.php';

require_jwt();

// Ensure user is admin and it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$_SESSION['is_admin']) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access Denied');
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'];

try {
    // CSRF validation
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('Invalid request token');
    }
    // Validate content ID
    if (empty($_POST['content_id']) || !is_numeric($_POST['content_id'])) {
        throw new Exception('Invalid content ID');
    }
    
    $content_id = (int)$_POST['content_id'];
    $user_id = $_SESSION['user_id'];
    
    // Check if content exists and get details (for file deletion)
    $content = $db->selectOne('SELECT * FROM content WHERE id = ?', [$content_id]);
    
    if (!$content) {
        throw new Exception('Content not found');
    }
    
    // Delete associated media file if exists
    if (!empty($content['featured_image'])) {
        $file_path = __DIR__ . '/../../' . $content['featured_image'];
        if (file_exists($file_path)) {
            @unlink($file_path);
        }
    }
    
    // Delete from database
    $db->delete('content', ['id' => $content_id]);
    
    // Log the action
    $db->insert('activity_logs', [
        'user_id' => $user_id,
        'action' => 'delete_content',
        'description' => "Deleted content: {$content['title']}",
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    $_SESSION['success'] = 'Content deleted successfully';
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

// Redirect back to content list
header('Location: ../content.php');
exit();
