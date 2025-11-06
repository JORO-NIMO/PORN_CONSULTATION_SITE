<?php
require_once '../config/config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // CSRF validation
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request token';
        if (isAjax()) {
            jsonResponse(['success' => false, 'errors' => $errors], 403);
        }
    }
    
    // Rate limiting per IP+email to mitigate brute force
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = strtolower($email) . '|' . $ip;
    if (!isset($_SESSION['login_attempts'])) { $_SESSION['login_attempts'] = []; }
    $attempt = $_SESSION['login_attempts'][$key] ?? ['count' => 0, 'lock_until' => 0];
    if ($attempt['lock_until'] > time()) {
        $errors[] = 'Too many attempts. Please try again later.';
        if (isAjax()) {
            jsonResponse(['success' => false, 'errors' => $errors], 429);
        }
    }
    
    if (empty($email) || empty($password)) {
        $errors[] = 'Email and password are required';
    } else {
        $db = Database::getInstance();
        $user = $db->fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Successful login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username']; // Changed from 'name' to 'username'
            $_SESSION['user_email'] = $user['email'];
            
            // Update last login
            $db->query("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
            
            // Create session record
            $sessionId = generateToken(64);
            $db->query(
                "INSERT INTO sessions (id, user_id, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))",
                [$sessionId, $user['id'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], SESSION_LIFETIME]
            );
            
            $isAdminUser = false;
            if (defined('ADMIN_EMAIL') && filter_var(ADMIN_EMAIL, FILTER_VALIDATE_EMAIL)) {
                $isAdminUser = (strtolower($user['email']) === strtolower(ADMIN_EMAIL));
            }
            $_SESSION['is_admin'] = $isAdminUser ? 1 : 0;
            // Reset rate limiting on success
            $_SESSION['login_attempts'][$key] = ['count' => 0, 'lock_until' => 0];

            if (isAjax()) {
                jsonResponse(['success' => true, 'redirect' => $isAdminUser ? '../admin/dashboard.php' : '../dashboard.php']);
            } else {
                header('Location: ' . ($isAdminUser ? '../admin/dashboard.php' : '../dashboard.php'));
                exit;
            }
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
    
    if (isAjax() && !empty($errors)) {
        jsonResponse(['success' => false, 'errors' => $errors], 401);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Welcome Back</h1>
                <p>Login to continue your recovery journey</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo sanitize($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form id="loginForm" method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo sanitize($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            
            <div class="auth-footer">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/auth.js"></script>
</body>
</html>
