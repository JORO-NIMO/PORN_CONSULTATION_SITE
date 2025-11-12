<?php
// Reusable API helper functions for content sections
// Provides: Affirmations, ZenQuotes, Quotable, Wikipedia summaries, Guardian articles

// Basic JSON fetch with SSL lenience and timeout
function ah_fetch_json($url, $timeout = 10) {
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
        'http' => [
            'timeout' => $timeout,
        ],
    ]);
    $raw = @file_get_contents($url, false, $context);
    if ($raw === false) return null;
    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

// Simple file-based cache
function ah_cache_get($key, $ttlSeconds = 1800) {
    $dir = __DIR__ . '/../data/cache';
    if (!is_dir($dir)) {@mkdir($dir, 0777, true);}    
    $file = $dir . '/' . preg_replace('/[^a-z0-9_-]/i', '_', $key) . '.json';
    if (file_exists($file) && (time() - filemtime($file) < $ttlSeconds)) {
        $json = @file_get_contents($file);
        $data = json_decode($json, true);
        if ($data) return $data;
    }
    return null;
}

function ah_cache_set($key, $value) {
    $dir = __DIR__ . '/../data/cache';
    if (!is_dir($dir)) {@mkdir($dir, 0777, true);}    
    $file = $dir . '/' . preg_replace('/[^a-z0-9_-]/i', '_', $key) . '.json';
    @file_put_contents($file, json_encode($value));
}

// Affirmations (returns array of strings)
function getAffirmations($count = 2) {
    $key = 'affirmations_' . $count;
    $cached = ah_cache_get($key, 1800);
    if ($cached) return $cached;

    $affirmations = [];
    for ($i = 0; $i < $count; $i++) {
        $data = ah_fetch_json('https://www.affirmations.dev/');
        if ($data && !empty($data['affirmation'])) {
            $affirmations[] = $data['affirmation'];
        }
    }
    if (empty($affirmations)) {
        $affirmations = [
            'You are capable of growth.',
            'Small steps lead to big changes.'
        ];
    }
    ah_cache_set($key, $affirmations);
    return $affirmations;
}

// ZenQuotes (returns array of [q, a])
function getZenQuotes($count = 2) {
    $key = 'zenquotes_all';
    $all = ah_cache_get($key, 3600);
    if (!$all) {
        $all = ah_fetch_json('https://zenquotes.io/api/quotes');
        if (!is_array($all)) {
            $all = [
                ['q' => 'Peace comes from within. Do not seek it without.', 'a' => 'Buddha'],
                ['q' => 'The only way out is through.', 'a' => 'Robert Frost']
            ];
        }
        ah_cache_set($key, $all);
    }
    // Pick random distinct items
    shuffle($all);
    return array_slice($all, 0, max(1, $count));
}

// Quotable quotes (returns array of objects with content/author)
function getQuotableQuotes($count = 2) {
    $key = 'quotable_random_4';
    $data = ah_cache_get($key, 1800);
    if (!$data) {
        $data = ah_fetch_json('https://api.quotable.io/quotes/random?limit=4&tags=inspirational|wisdom|life');
        if (!is_array($data)) {
            $data = [
                ['content' => 'The journey of a thousand miles begins with one step.', 'author' => 'Lao Tzu'],
                ['content' => 'What you do today can improve all your tomorrows.', 'author' => 'Ralph Marston'],
                ['content' => 'Act as if what you do makes a difference. It does.', 'author' => 'William James'],
                ['content' => 'Keep your face always toward the sunshine—and shadows will fall behind you.', 'author' => 'Walt Whitman']
            ];
        }
        ah_cache_set($key, $data);
    }
    return array_slice($data, 0, max(1, $count));
}

// Wikipedia summaries (returns array of ['title','extract','thumbnail'])
function getWikipediaSummaries($topics = null, $count = 2) {
    $defaultTopics = [
        'Mental health', 'Mindfulness', 'Cognitive behavioral therapy', 'Resilience'
    ];
    $topics = $topics && is_array($topics) ? $topics : $defaultTopics;
    shuffle($topics);
    $picked = array_slice($topics, 0, max(1, $count));
    $results = [];
    foreach ($picked as $title) {
        $urlTitle = rawurlencode($title);
        $data = ah_fetch_json("https://en.wikipedia.org/api/rest_v1/page/summary/{$urlTitle}");
        if ($data && !empty($data['title'])) {
            $results[] = [
                'title' => $data['title'],
                'extract' => $data['extract'] ?? '',
                'thumbnail' => $data['thumbnail']['source'] ?? ''
            ];
        }
    }
    if (empty($results)) {
        $results = [
            ['title' => 'Mental health', 'extract' => 'Mental health includes our emotional, psychological, and social well-being.', 'thumbnail' => ''],
            ['title' => 'Mindfulness', 'extract' => 'Mindfulness is the practice of purposely bringing one’s attention to the present moment.', 'thumbnail' => '']
        ];
    }
    return $results;
}

// Guardian articles via Open Platform (uses public test key; low volume)
function getGuardianArticles($count = 2) {
    $key = 'guardian_latest_5';
    $data = ah_cache_get($key, 1800);
    if (!$data) {
        $url = 'https://content.guardianapis.com/search?order-by=newest&show-fields=trailText,thumbnail&api-key=test&page-size=5';
        $res = ah_fetch_json($url);
        if ($res && isset($res['response']['results'])) {
            $data = $res['response']['results'];
        } else {
            $data = []; // Ensure $data is an array even if fetch fails
        }
        if (empty($data)) {
            $data = [
                ['webTitle' => 'Wellbeing: creating daily habits', 'webUrl' => 'https://www.theguardian.com/', 'fields' => ['trailText' => 'Healthy routines', 'thumbnail' => '']],
                ['webTitle' => 'Community mental health support', 'webUrl' => 'https://www.theguardian.com/', 'fields' => ['trailText' => 'Support networks matter', 'thumbnail' => '']]
            ];
        }
        ah_cache_set($key, $data);
    }
    return array_slice($data, 0, max(1, $count));
}

?>