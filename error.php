<?php
/**
 * Error Handling Page
 * 
 * This script handles all error pages (400, 401, 403, 404, 500, etc.)
 * and displays a user-friendly message while logging the error.
 */

require_once __DIR__ . '/config/config.php';

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: no-referrer');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' https://*.x.com; style-src \'self\' 'unsafe-inline'; img-src \'self\' data: https:; font-src \'self\' data:;');

// Get error code from query string or default to 500
$errorCode = isset($_GET['code']) ? (int)$_GET['code'] : 500;

// Set HTTP status code
$statusCodes = [
    400 => 'Bad Request',
    401 => 'Unauthorized',
    403 => 'Forbidden',
    404 => 'Not Found',
    500 => 'Internal Server Error',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Timeout'
];

// Default to 500 if invalid error code
if (!isset($statusCodes[$errorCode])) {
    $errorCode = 500;
}

$errorMessage = $statusCodes[$errorCode];
http_response_code($errorCode);

// Log the error (in a real app, you'd want to log more details)
error_log(sprintf(
    "[%s] %s: %s %s",
    date('Y-m-d H:i:s'),
    $errorCode,
    $errorMessage,
    $_SERVER['REQUEST_URI'] ?? ''
));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Error <?php echo $errorCode; ?> - <?php echo SITE_NAME; ?></title>
    <style>
        :root {
            --primary-color: #3a86ff;
            --error-color: #ff4d4d;
            --text-color: #333;
            --bg-color: #f8f9fa;
            --card-bg: #fff;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--bg-color);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .error-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2rem;
            text-align: center;
        }

        .error-code {
            font-size: 6rem;
            font-weight: 700;
            color: var(--error-color);
            line-height: 1;
            margin-bottom: 1rem;
        }

        .error-message {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: var(--text-color);
        }

        .error-description {
            margin-bottom: 2rem;
            color: #666;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .btn {
            display: inline-block;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            font-weight: 500;
            transition: background-color 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background: #2a75f5;
            text-decoration: none;
        }

        .footer {
            text-align: center;
            margin-top: 2rem;
            color: #666;
            font-size: 0.875rem;
        }

        @media (max-width: 768px) {
            .error-code {
                font-size: 4rem;
            }
            
            .error-message {
                font-size: 1.25rem;
            }
            
            .container {
                padding: 10px;
            }
            
            .error-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-card">
            <div class="error-code"><?php echo $errorCode; ?></div>
            <h1 class="error-message"><?php echo htmlspecialchars($errorMessage); ?></h1>
            
            <div class="error-description">
                <?php if ($errorCode === 400): ?>
                    <p>Your browser sent a request that this server could not understand.</p>
                <?php elseif ($errorCode === 401): ?>
                    <p>You are not authorized to access this page. Please log in with the correct credentials.</p>
                <?php elseif ($errorCode === 403): ?>
                    <p>You don't have permission to access this resource.</p>
                <?php elseif ($errorCode === 404): ?>
                    <p>The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
                <?php else: ?>
                    <p>An unexpected error occurred on our server. Please try again later.</p>
                <?php endif; ?>
            </div>
            
            <a href="/" class="btn">Return to Homepage</a>
        </div>
    </div>
    
    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
    </footer>
</body>
</html>
