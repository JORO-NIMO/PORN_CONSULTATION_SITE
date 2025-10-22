<?php
// Include TwitterOAuth
require_once __DIR__ . '/../vendor/abraham/twitteroauth-main/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterClient {
    private $connection;
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        
        // Load API keys
        $apiKeysFile = __DIR__ . '/../config/twitter_api_keys.php';
        if (!file_exists($apiKeysFile)) {
            throw new Exception('Twitter API keys not found. Please copy config/twitter_api_keys.php to config/api_keys.php and add your credentials.');
        }
        $apiKeys = require $apiKeysFile;
        $twitterKeys = $apiKeys['twitter'];
        
        // Initialize Twitter OAuth
        $this->connection = new TwitterOAuth(
            $twitterKeys['api_key'],
            $twitterKeys['api_secret_key'],
            $twitterKeys['access_token'],
            $twitterKeys['access_token_secret']
        );
    }
    
    /**
     * Search for tweets with specific hashtags
     */
    public function searchTweets($query = '#Recovery OR #PornAddictionRecovery', $count = 50) {
        try {
            $tweets = $this->connection->get('search/tweets', [
                'q' => $query,
                'count' => $count,
                'tweet_mode' => 'extended',
                'result_type' => 'recent',
                'include_entities' => true
            ]);
            
            if (isset($tweets->errors)) {
                throw new Exception('Twitter API Error: ' . $tweets->errors[0]->message);
            }
            
            return $tweets->statuses ?? [];
        } catch (Exception $e) {
            error_log('Twitter search error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Save tweets to database
     */
    public function saveTweets($tweets) {
        if (empty($tweets)) return 0;
        
        $saved = 0;
        foreach ($tweets as $tweet) {
            // Skip retweets
            if (isset($tweet->retweeted_status)) continue;
            
            $tweetData = [
                'tweet_id' => $tweet->id_str,
                'user_id' => $tweet->user->id_str,
                'user_name' => $tweet->user->screen_name,
                'user_display_name' => $tweet->user->name,
                'user_avatar' => $tweet->user->profile_image_url_https,
                'text' => $tweet->full_text ?? $tweet->text,
                'media_url' => $this->extractMediaUrl($tweet),
                'url' => 'https://x.com/' . $tweet->user->screen_name . '/status/' . $tweet->id_str,
                'created_at' => date('Y-m-d H:i:s', strtotime($tweet->created_at)),
                'retweet_count' => $tweet->retweet_count,
                'favorite_count' => $tweet->favorite_count,
                'is_active' => 1
            ];
            
            // Check if tweet already exists
            $exists = $this->db->fetchOne(
                'SELECT id FROM scraped_twitter WHERE tweet_id = ?',
                [$tweetData['tweet_id']]
            );
            
            if (!$exists) {
                $this->db->insert('scraped_twitter', $tweetData);
                $saved++;
            }
        }
        
        return $saved;
    }
    
    /**
     * Extract media URL from tweet
     */
    private function extractMediaUrl($tweet) {
        if (!empty($tweet->extended_entities->media)) {
            foreach ($tweet->extended_entities->media as $media) {
                if ($media->type === 'photo') {
                    return $media->media_url_https;
                }
            }
        }
        return null;
    }
    
    /**
     * Get latest tweets from database
     */
    public function getLatestTweets($limit = 10) {
        return $this->db->fetchAll(
            'SELECT * FROM scraped_twitter 
             WHERE is_active = 1 
             ORDER BY created_at DESC 
             LIMIT ?',
            [$limit]
        );
    }
}
