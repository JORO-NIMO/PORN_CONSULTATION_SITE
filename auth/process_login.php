<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_helpers.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    // Basic validation
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Please enter both email and password';
        header('Location: login.php');
        exit();
    }
    
    try {
        // Get user from database (use password_hash column)
        $user = $db->selectOne(
            'SELECT id, email, password_hash, role FROM users WHERE email = ?', 
            [$email]
        );
        
        // Verify user exists and password is correct
        if ($user && password_verify($password, $user['password_hash'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'] ?: 'user';
            
            // Update last login
            $db->update('users', 
                ['last_login' => date('Y-m-d H:i:s')], 
                ['id' => $user['id']]
            );

            // Optional: notify admin of login event (basic security monitoring)
            try {
                if (defined('ADMIN_EMAIL') && filter_var(ADMIN_EMAIL, FILTER_VALIDATE_EMAIL)) {
                    require_once __DIR__ . '/../includes/mail_helper.php';
                    $siteName = defined('SITE_NAME') ? SITE_NAME : 'Website';
                    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                    $adminHtml = '<p>User logged in:</p>' .
                                 '<ul><li>Email: ' . htmlspecialchars($user['email']) . '</li>' .
                                 '<li>Time: ' . date('Y-m-d H:i:s') . '</li>' .
                                 '<li>IP: ' . htmlspecialchars($ip) . '</li>' .
                                 '<li>UA: ' . htmlspecialchars($ua) . '</li></ul>';
                    @send_mail_safe(ADMIN_EMAIL, '[' . $siteName . '] Login notification', $adminHtml);
                }
            } catch (Throwable $e) {
                error_log('Login mail error: ' . $e->getMessage());
            }
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: /admin/dashboard.php');
            } else {
                header('Location: /dashboard.php');
            }
            exit();
        } else {
            $_SESSION['error'] = 'Invalid email or password';
            header('Location: login.php');
            exit();
        }
    } catch (Exception $e) {
        error_log('Login error: ' . $e->getMessage());
        $_SESSION['error'] = 'An error occurred. Please try again later.';
        header('Location: login.php');
        exit();
    }
} else {
    // If not a POST request, redirect to login
    header('Location: login.php');
    exit();
}
