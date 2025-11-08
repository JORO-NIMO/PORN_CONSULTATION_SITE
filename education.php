<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

// Function to fetch articles from News API
function getNewsArticles($query, $cache_duration = 1800) { // 30 minutes cache
    $cache_file = __DIR__ . '/data/cache/' . preg_replace('/[^a-zA-Z0-9_-]/i', '_', $query) . '_news.json';

    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_duration) {
        return json_decode(file_get_contents($cache_file), true);
    }

    // Replace with your actual News API key
    $apiKey = 'YOUR_NEWS_API_KEY'; 
    $apiUrl = "https://newsapi.org/v2/everything?q=" . urlencode($query) . "&language=en&sortBy=relevancy&apiKey=" . $apiKey;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'MyMentalHealthApp/1.0 (https://example.com; mail@example.com)');
    $json = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($json, true);

    if (isset($data['articles']) && !empty($data['articles'])) {
        file_put_contents($cache_file, $json);
        return $data['articles'];
    }

    return [];
}

$educational_topics = ['mental health', 'psychology', 'therapy', 'well-being', 'mindfulness', 'addiction recovery'];
$news_articles = [];
foreach ($educational_topics as $topic) {
    $articles = getNewsArticles($topic);
    $news_articles = array_merge($news_articles, $articles);
}

// Remove duplicates based on article URL
$unique_articles = [];
$seen_urls = [];
foreach ($news_articles as $article) {
    if (!in_array($article['url'], $seen_urls)) {
        $unique_articles[] = $article;
        $seen_urls[] = $article['url'];
    }
}

// Limit to a reasonable number of articles
$news_articles = array_slice($unique_articles, 0, 12);

$db = Database::getInstance();

// Get single content if ID provided
$singleContent = null;
if (isset($_GET['id'])) {
    $singleContent = $db->fetchOne("SELECT * FROM educational_content WHERE id = ?", [intval($_GET['id'])]);
    if ($singleContent) {
        // Increment views
        $db->query("UPDATE educational_content SET views = views + 1 WHERE id = ?", [$singleContent['id']]);
    }
}

// Get all content by category
$categories = ['Science', 'Relationships', 'Statistics', 'Recovery', 'Mental Health', 'Health'];
$contentByCategory = [];
foreach ($categories as $cat) {
    $contentByCategory[$cat] = $db->fetchAll(
        "SELECT * FROM educational_content WHERE category = ? ORDER BY is_featured DESC, views DESC LIMIT 10",
        [$cat]
    );
}

// Get latest content
$latestContent = $db->fetchAll("SELECT * FROM educational_content ORDER BY created_at DESC LIMIT 6");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educational Resources - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="education-page">
        <div class="container">
            <?php if ($singleContent): ?>
            <!-- Single Content View -->
            <article class="content-article">
                <div class="article-header">
                    <a href="education.php" class="back-link">← Back to Resources</a>
                    <div class="content-type-badge large"><?php echo ucfirst($singleContent['content_type']); ?></div>
                    <h1><?php echo sanitize($singleContent['title']); ?></h1>
                    <div class="article-meta">
                        <span>📁 <?php echo sanitize($singleContent['category']); ?></span>
                        <span>👁️ <?php echo $singleContent['views']; ?> views</span>
                        <span>📅 <?php echo date('M d, Y', strtotime($singleContent['created_at'])); ?></span>
                    </div>
                </div>
                
                <div class="article-content">
                    <?php echo nl2br(sanitize($singleContent['content'])); ?>
                </div>
                
                <?php if ($singleContent['source_url']): ?>
                <div class="article-source">
                    <strong>Source:</strong> 
                    <a href="<?php echo sanitize($singleContent['source_url']); ?>" target="_blank" rel="noopener">
                        <?php echo sanitize($singleContent['source_url']); ?>
                    </a>
                </div>
                <?php endif; ?>
                
                <?php if ($singleContent['tags']): ?>
                <div class="article-tags">
                    <?php 
                    $tags = json_decode($singleContent['tags'], true);
                    foreach ($tags as $tag): 
                    ?>
                    <span class="tag">#<?php echo sanitize($tag); ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </article>
            
            <?php else: ?>
            <!-- Content Library View -->
            <div class="page-header">
                <h1>Educational Resources</h1>
                <p class="subtitle">Learn about the science, effects, and recovery from pornography addiction</p>
            </div>

            <!-- News API Articles -->
            <section class="content-section">
                <h2>Latest Educational Articles</h2>
                <div class="content-grid">
                    <?php foreach ($news_articles as $article): ?>
                    <div class="content-card">
                        <h3><?php echo sanitize($article['title']); ?></h3>
                        <p><?php echo substr(sanitize($article['description']), 0, 150); ?>...</p>
                        <div class="card-footer">
                            <a href="<?php echo sanitize($article['url']); ?>" class="btn btn-link" target="_blank" rel="noopener">Read More →</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            
            <!-- Animated Slideshow Section -->
            <section class="slideshow-section">
                <div class="slideshow-container">
                    <div class="slide active">
                        <div class="slide-content">
                            <h2>🧠 Your Brain Can Heal</h2>
                            <p>Neuroplasticity allows your brain to rewire and recover from addiction patterns</p>
                        </div>
                    </div>
                    <div class="slide">
                        <div class="slide-content">
                            <h2>💔 Real Relationships Matter</h2>
                            <p>Authentic intimacy and connection far surpass digital fantasy</p>
                        </div>
                    </div>
                    <div class="slide">
                        <div class="slide-content">
                            <h2>📊 You're Not Alone</h2>
                            <p>Millions struggle with this addiction - recovery is possible with support</p>
                        </div>
                    </div>
                    <div class="slide">
                        <div class="slide-content">
                            <h2>🌟 Freedom Awaits</h2>
                            <p>Every day of recovery brings you closer to the life you deserve</p>
                        </div>
                    </div>
                </div>
                <div class="slideshow-controls">
                    <button class="slide-btn prev">‹</button>
                    <div class="slide-indicators"></div>
                    <button class="slide-btn next">›</button>
                </div>
            </section>
            
            <!-- Latest Content (Removed - now using News API) -->
            <!-- Content by Category (Removed - now using News API) -->
            
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/slideshow.js"></script>
</body>
</html>
