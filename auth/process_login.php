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
    // CSRF validation
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Invalid request token';
        header('Location: login.php');
        exit();
    }

    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    // Basic validation
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Please enter both email and password';
        header('Location: login.php');
        exit();
    }
    
    // Rate limiting per IP+email to mitigate brute force
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = strtolower($email) . '|' . $ip;
    if (!isset($_SESSION['login_attempts'])) { $_SESSION['login_attempts'] = []; }
    $attempt = $_SESSION['login_attempts'][$key] ?? ['count' => 0, 'lock_until' => 0];
    if ($attempt['lock_until'] > time()) {
        $_SESSION['error'] = 'Too many attempts. Please try again later.';
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
            // Successful login: rotate session ID
            session_regenerate_id(true);
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'] ?: 'user';
            // Reset rate limiting counter for this key
            unset($_SESSION['login_attempts'][$key]);
            
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
            
            // Redirect based on admin email (strict)
            try {
                $isAdminUser = false;
                if (defined('ADMIN_EMAIL') && filter_var(ADMIN_EMAIL, FILTER_VALIDATE_EMAIL)) {
                    $isAdminUser = (strtolower($user['email']) === strtolower(ADMIN_EMAIL));
                }
                // Persist convenience flag
                $_SESSION['is_admin'] = $isAdminUser ? 1 : 0;
                if ($isAdminUser) {
                    header('Location: /admin/dashboard.php');
                } else {
                    header('Location: /dashboard.php');
                }
            } catch (Throwable $e) {
                error_log('Post-login redirect error: ' . $e->getMessage());
                header('Location: /dashboard.php');
            }
            exit();
        } else {
            // Increment attempt count and enforce lockout if needed
            $attempt['count']++;
            if (defined('MAX_LOGIN_ATTEMPTS') && defined('LOGIN_LOCKOUT_TIME') && $attempt['count'] >= MAX_LOGIN_ATTEMPTS) {
                $attempt['lock_until'] = time() + LOGIN_LOCKOUT_TIME;
                $attempt['count'] = 0;
            }
            $_SESSION['login_attempts'][$key] = $attempt;

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
