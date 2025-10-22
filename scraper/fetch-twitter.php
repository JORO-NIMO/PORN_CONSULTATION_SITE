<?php
/**
 * Twitter Fetcher Script
 * Run this script manually or via cron to fetch latest tweets
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/TwitterClient.php';

// Set time limit to 5 minutes
set_time_limit(300);

// Log function
function logMessage($message) {
    $logFile = __DIR__ . '/twitter_scraper.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

try {
    logMessage("Starting Twitter fetch...");
    
    $twitter = new TwitterClient();
    
    // Search for recovery-related tweets
    $tweets = $twitter->searchTweets(
        '#Recovery OR #PornAddictionRecovery OR #NoFap OR #PornFree OR #AddictionRecovery -filter:retweets',
        50 // Number of tweets to fetch
    );
    
    logMessage(sprintf("Found %d tweets", count($tweets)));
    
    // Save tweets to database
    $saved = $twitter->saveTweets($tweets);
    
    logMessage("Saved $saved new tweets to database");
    
    // Update existing tweets (like/retweet counts)
    $updated = $twitter->updateTweetMetrics();
    logMessage("Updated metrics for $updated tweets");
    
    logMessage("Twitter fetch completed successfully");
    echo "Successfully fetched and saved $saved new tweets\n";
    
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
    logMessage($error);
    echo $error . "\n";
    exit(1);
}
