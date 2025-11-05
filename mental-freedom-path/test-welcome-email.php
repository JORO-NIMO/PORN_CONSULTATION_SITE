<?php
/**
 * Test script for welcome email functionality
 * Run this script to test if the Brevo email integration is working
 */

// Set up paths
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('VIEWS_PATH', APP_PATH . '/views');
define('CONFIG_PATH', ROOT_PATH . '/config');

// Load configuration
require_once ROOT_PATH . '/config/config.php';

// Register autoloader for App namespace
spl_autoload_register(function ($class) {
    if (strpos($class, 'App\\') === 0) {
        $classPath = str_replace('App\\', '', $class);
        $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $classPath);
        $file = APP_PATH . DIRECTORY_SEPARATOR . $classPath . '.php';
        
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

// Try to load Composer autoloader if it exists (but don't require it)
$composerAutoload = ROOT_PATH . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

use App\Core\Mailer;

echo "<h2>Testing Welcome Email Functionality</h2>\n";

// Test email configuration
echo "<h3>Email Configuration:</h3>\n";
echo "BREVO_API_KEY: " . (BREVO_API_KEY ? "✓ Set" : "✗ Not set") . "<br>\n";
echo "MAIL_FROM: " . MAIL_FROM . "<br>\n";
echo "MAIL_FROM_NAME: " . MAIL_FROM_NAME . "<br>\n";
echo "MAIL_HOST: " . MAIL_HOST . "<br>\n";
echo "MAIL_PORT: " . MAIL_PORT . "<br>\n";

// Test email sending
echo "<h3>Testing Email Send:</h3>\n";

try {
    $mailer = new Mailer();
    
    // Test data
    $testEmail = "test@example.com"; // Change this to your email for testing
    $testData = [
        'name' => 'Test User',
        'email' => $testEmail
    ];
    
    echo "Attempting to send welcome email to: {$testEmail}<br>\n";
    
    $result = $mailer->send(
        $testEmail,
        'Welcome to Mental Freedom Path - Thank You for Signing Up!',
        'emails/welcome',
        $testData
    );
    
    if ($result) {
        echo "✓ <strong>Success!</strong> Welcome email sent successfully.<br>\n";
        echo "Check your email inbox for the welcome message.<br>\n";
    } else {
        echo "✗ <strong>Failed!</strong> Could not send welcome email.<br>\n";
    }
    
} catch (Exception $e) {
    echo "✗ <strong>Error:</strong> " . $e->getMessage() . "<br>\n";
}

echo "<h3>Next Steps:</h3>\n";
echo "1. If the test was successful, try registering a new user to see the automatic welcome email<br>\n";
echo "2. If there were errors, check your Brevo API key and email configuration<br>\n";
echo "3. Make sure your sender email (9aa637001@smtp-brevo.com) is verified in your Brevo account<br>\n";

echo "<p><em>Note: Change the \$testEmail variable in this script to your actual email address to receive the test email.</em></p>\n";
?>