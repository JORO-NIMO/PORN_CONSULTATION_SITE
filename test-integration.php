<?php
// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' https://*.x.com; style-src \'self\' 'unsafe-inline'; img-src \'self\' data: https:; font-src \'self\' data:;');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Prevent direct access to this file
if (!defined('ENVIRONMENT') || ENVIRONMENT !== 'development') {
    header('HTTP/1.1 403 Forbidden');
    die('Access denied');
}

// Include required files
require_once 'config/database.php';
require_once 'includes/TwitterClient.php';

// Initialize database
$db = Database::getInstance();

// Test database connection
try {
    $db->query('SELECT 1');
    echo "✅ Database connection successful<br>";
    
    // Check if table exists
    $tables = $db->fetchAll("SELECT name FROM sqlite_master WHERE type='table' AND name='scraped_twitter'");
    if (empty($tables)) {
        // Create table if it doesn't exist
        $db->exec(file_get_contents('migrations/002_create_twitter_tables_fixed.php'));
        echo "✅ Created scraped_twitter table<br>";
    } else {
        echo "✅ scraped_twitter table exists<br>";
    }
} catch (Exception $e) {
    die("❌ Database error: " . htmlspecialchars($e->getMessage()));
}

// Test Twitter API
try {
    $twitter = new TwitterClient();
    echo "✅ Twitter client initialized successfully<br>";
    
    // Test search with limit 1 to avoid rate limiting
    $tweets = $twitter->searchTweets('#recovery', 1);
    
    if (is_array($tweets)) {
        echo "✅ Twitter search successful. Found " . count($tweets) . " tweets<br>";
        
        // Test saving tweets
        $saved = $twitter->saveTweets($tweets);
        echo "✅ Successfully saved $saved tweets to database<br>";
        
        // Test retrieving tweets
        $latestTweets = $twitter->getLatestTweets(5);
        echo "✅ Retrieved " . count($latestTweets) . " tweets from database<br>";
    } else {
        echo "❌ Twitter search returned no results<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Twitter error: " . htmlspecialchars($e->getMessage()) . "<br>";
    error_log('Twitter error: ' . $e->getMessage());
}

// Security check: Verify file permissions
$dbFile = __DIR__ . '/data/database.sqlite';
if (file_exists($dbFile)) {
    $perms = substr(sprintf('%o', fileperms($dbFile)), -4);
    echo "ℹ️ Database file permissions: $perms (should be 0644)<br>";
}

// Check for common security issues
$securityIssues = [];

// Check if display_errors is on
if (ini_get('display_errors')) {
    $securityIssues[] = 'display_errors should be off in production';
}

// Check if error reporting is too verbose
if (error_reporting() & E_ALL) {
    $securityIssues[] = 'Error reporting should be more restrictive in production';
}

// Check for open base directory
if (!ini_get('open_basedir')) {
    $securityIssues[] = 'open_basedir should be set to restrict file access';
}

// Display security issues
if (!empty($securityIssues)) {
    echo "<h3>⚠️ Security Recommendations:</h3><ul>";
    foreach ($securityIssues as $issue) {
        echo "<li>" . htmlspecialchars($issue) . "</li>";
    }
    echo "</ul>";
}

// Test completed
echo "<h3>✅ Integration test completed</h3>";
?>

<style>
    body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
</style>
