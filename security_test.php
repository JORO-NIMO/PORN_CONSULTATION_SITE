<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test database connection
try {
    require_once 'config/database.php';
    $db = Database::getInstance();
    echo "✅ Database connection successful<br>";
    
    // Test if table exists
    $tables = $db->fetchAll("SELECT name FROM sqlite_master WHERE type='table' AND name='scraped_twitter'");
    echo $tables ? "✅ scraped_twitter table exists<br>" : "❌ scraped_twitter table missing<br>";
    
} catch (Exception $e) {
    die("❌ Database error: " . htmlspecialchars($e->getMessage()));
}

// Test API keys
$apiKeys = @include 'config/twitter_api_keys.php';
if (!is_array($apiKeys) || empty($apiKeys['twitter']['api_key'])) {
    die("❌ API keys not properly configured");
}
echo "✅ API keys loaded successfully<br>";

// Test Twitter client
try {
    require_once 'includes/TwitterClient.php';
    $twitter = new TwitterClient();
    echo "✅ Twitter client initialized successfully<br>";
    
    // Test search (limit to 1 tweet for testing)
    $tweets = $twitter->searchTweets('#recovery', 1);
    echo is_array($tweets) ? "✅ Twitter search successful<br>" : "❌ Twitter search failed<br>";
    
} catch (Exception $e) {
    echo "❌ Twitter client error: " . htmlspecialchars($e->getMessage()) . "<br>";
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' https://*.x.com; style-src \'self\' 'unsafe-inline'; img-src \'self\' data: https:; font-src \'self\' data:;');
?>
