<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/csrf_functions.php';

$errors = [];

// The PHP login processing logic is now handled by process_login.php
// This file is purely for displaying the login form.

// Generate a new CSRF token for the form
$_SESSION['csrf_token'] = generateCSRFToken();

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
            
            <div class="login-container">
                <form id="loginForm" class="login-form">
                    <h2>Login</h2>
                    <?php if (isset($_GET['expired'])): ?>
                        <p class="error-message">Your session has expired. Please log in again.</p>
                    <?php endif; ?>
                    <?php if (isset($_GET['error'])): ?>
                        <p class="error-message"><?= htmlspecialchars($_GET['error']) ?></p>
                    <?php endif; ?>
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="input-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="input-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" class="login-button">Login</button>
                </form>
            </div>
            
        </div>
    </div>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const form = event.target;
            const formData = new FormData(form);

            fetch('process_login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    localStorage.setItem('jwt_token', data.jwt);
                    window.location.href = data.redirect;
                } else {
                    alert(data.errors.join('\n'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred during login.');
            });
        });
    </script>
</body>
</html>
