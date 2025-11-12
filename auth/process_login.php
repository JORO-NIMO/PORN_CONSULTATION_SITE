<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/csrf_functions.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Composer autoloader

use Firebase\JWT\JWT;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // CSRF validation
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request token';
    }
    
    // Rate limiting per IP+email to mitigate brute force
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = strtolower($email) . '|' . $ip;
    if (!isset($_SESSION['login_attempts'])) { $_SESSION['login_attempts'] = []; }
    $attempt = $_SESSION['login_attempts'][$key] ?? ['count' => 0, 'lock_until' => 0];
    if ($attempt['lock_until'] > time()) {
        $errors[] = 'Too many attempts. Please try again later.';
    }
    
    if (empty($email) || empty($password)) {
        $errors[] = 'Email and password are required';
    } else {
        $db = Database::getInstance();
        $user = $db->fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Successful login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            
            // Update last login
            $db->query("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
            
            // Create session record
            $sessionId = generateToken(64);
            $db->query(
                "INSERT INTO sessions (id, user_id, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))",
                [$sessionId, $user['id'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], SESSION_LIFETIME]
            );
            
            // Reset rate limiting on success
            $_SESSION['login_attempts'][$key] = ['count' => 0, 'lock_until' => 0];

            // Generate JWT
            $issuedAt = time();
            $expirationTime = $issuedAt + SESSION_LIFETIME; // JWT valid for SESSION_LIFETIME seconds
            $payload = [
                'iat' => $issuedAt, // Issued at: time when the token was generated
                'exp' => $expirationTime, // Expiration time
                'data' => [
                    'userId' => $user['id'],
                    'email' => $user['email'],
                    'username' => $user['username']
                ]
            ];

            $jwt = JWT::encode($payload, JWT_SECRET, 'HS256');

            // Return JWT as JSON
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'jwt' => $jwt, 'redirect' => '../dashboard.php']);
            exit;
        } else {
            $errors[] = 'Invalid email or password';
            // Increment attempts and possibly lock
            $attempt['count'] = ($attempt['count'] ?? 0) + 1;
            if ($attempt['count'] >= MAX_LOGIN_ATTEMPTS) {
                $attempt['lock_until'] = time() + LOGIN_LOCKOUT_TIME;
            }
            $_SESSION['login_attempts'][$key] = $attempt;
        }
    }
}

if (!empty($errors)) {
    $error_string = implode('&', array_map(function($e) { return 'error[]=' . urlencode($e); }, $errors));
    header('Location: login.php?' . $error_string);
    exit;
}
