<?php
require_once '../config/config.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request token';
        if (isAjax()) {
            jsonResponse(['success' => false, 'errors' => $errors], 403);
        }
    }

    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters';
    if ($password !== $confirm_password) $errors[] = 'Passwords do not match';
    
    if (empty($errors)) {
        $db = Database::getInstance();
        
        // Check if email exists
        $existing = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            $errors[] = 'Email already registered';
        } else {
            // Create user with role and timestamps
            $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
            $db->query(
                "INSERT INTO users (username, email, password_hash, role, created_at) VALUES (?, ?, ?, 'user', NOW())",
                [$name, $email, $passwordHash]
            );

            // Send welcome email to user and notify admin
            try {
                require_once __DIR__ . '/../includes/mail_helper.php';
                $siteName = defined('SITE_NAME') ? SITE_NAME : 'Our Site';
                $welcomeHtml = '<h2>Welcome to ' . htmlspecialchars($siteName) . '</h2>' .
                               '<p>Hi ' . htmlspecialchars($name) . ',</p>' .
                               '<p>Your account has been created successfully. You can login anytime:</p>' .
                               '<p><a href="' . htmlspecialchars(SITE_URL) . '/auth/login.php">Login</a></p>' .
                               '<p>— ' . htmlspecialchars($siteName) . ' Team</p>';
                @send_mail_safe($email, 'Welcome to ' . $siteName, $welcomeHtml);

                // Admin notification
                if (defined('ADMIN_EMAIL') && filter_var(ADMIN_EMAIL, FILTER_VALIDATE_EMAIL)) {
                    $adminHtml = '<p>New user registered:</p>' .
                                 '<ul><li>Name: ' . htmlspecialchars($name) . '</li>' .
                                 '<li>Email: ' . htmlspecialchars($email) . '</li></ul>' .
                                 '<p>Time: ' . date('Y-m-d H:i:s') . '</p>';
                    @send_mail_safe(ADMIN_EMAIL, '[' . $siteName . '] New user signup', $adminHtml);
                }
            } catch (Throwable $e) {
                error_log('Welcome/Admin mail error: ' . $e->getMessage());
            }

            $success = true;
            
            if (isAjax()) {
                jsonResponse(['success' => true, 'message' => 'Registration successful']);
            }
        }
    }
    
    if (isAjax() && !empty($errors)) {
        jsonResponse(['success' => false, 'errors' => $errors], 400);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Create Your Account</h1>
                <p>Join our community and start your recovery journey</p>
            </div>
            
            <?php if ($success): ?>
                <div class="alert success">
                    Registration successful! <a href="auth/login.php">Login now</a>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo sanitize($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form id="registerForm" method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo sanitize($_POST['name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo sanitize($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           minlength="8" placeholder="At least 8 characters">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </form>
            
            <div class="auth-footer">
                Already have an account? <a href="auth/login.php">Login here</a>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/auth.js"></script>
</body>
</html>
