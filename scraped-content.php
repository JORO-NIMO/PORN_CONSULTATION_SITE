<?php
require_once 'config/config.php';

$db = Database::getInstance();

// Get latest scraped content
$videos = $db->fetchAll(
    "SELECT * FROM scraped_videos WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6",
    []
);

$research = $db->fetchAll(
    "SELECT * FROM scraped_research WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6",
    []
);

$articles = $db->fetchAll(
    "SELECT * FROM scraped_articles WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6",
    []
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Latest Content - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
    .content-page {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #06b6d4 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }
    .content-page .page-header {
        text-align: center;
        margin-bottom: 3rem;
        color: white;
    }
    .content-page .page-header h1 {
        font-size: 3rem;
        margin-bottom: 1rem;
        text-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
        color: white;
    }
    .content-page .page-header .subtitle {
        font-size: 1.25rem;
        color: rgba(255, 255, 255, 0.9);
    }
    .content-section {
        background: white;
        border-radius: 20px;
        padding: 3rem;
        margin-bottom: 3rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    }
    .content-section h2 {
        color: var(--dark);
        margin-bottom: 2rem;
        font-size: 2rem;
        border-bottom: 3px solid var(--primary);
        padding-bottom: 1rem;
    }
    .content-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
    }
    .content-card {
        border: 2px solid var(--border);
        border-radius: 12px;
        padding: 1.5rem;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .content-card:hover {
        border-color: var(--primary);
        box-shadow: 0 8px 25px rgba(99, 102, 241, 0.15);
        transform: translateY(-5px);
    }
    .content-card h3 {
        color: var(--dark);
        margin-bottom: 1rem;
        font-size: 1.25rem;
    }
    .content-card p {
        color: var(--text);
        line-height: 1.6;
        margin-bottom: 1rem;
    }
    .content-meta {
        color: var(--text-light);
        font-size: 0.875rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border);
    }
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-right: 0.5rem;
    }
    .badge-youtube {
        background: #ff0000;
        color: white;
    }
    .badge-research {
        background: #4caf50;
        color: white;
    }
    .badge-article {
        background: #2196f3;
        color: white;
    }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="content-page">
        <div class="container">
            <div class="page-header">
                <h1>Latest Content</h1>
                <p class="subtitle">Automatically updated daily with the latest research, videos, and articles</p>
            </div>
            
            <?php if (!empty($videos)): ?>
            <div class="content-section">
                <h2>📺 Latest Recovery Videos</h2>
                <div class="content-grid">
                    <?php foreach ($videos as $video): ?>
                    <div class="content-card" onclick="window.open('https://www.youtube.com/watch?v=<?php echo $video['youtube_id']; ?>', '_blank')">
                        <span class="badge badge-youtube">YouTube</span>
                        <h3><?php echo sanitize($video['title']); ?></h3>
                        <p><?php echo sanitize(substr($video['description'], 0, 150)); ?>...</p>
                        <div class="content-meta">
                            <div>📺 <?php echo sanitize($video['channel']); ?></div>
                            <div>📅 <?php echo date('M d, Y', strtotime($video['published_at'])); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($research)): ?>
            <div class="content-section">
                <h2>📚 Latest Research Papers</h2>
                <div class="content-grid">
                    <?php foreach ($research as $paper): ?>
                    <div class="content-card" onclick="window.open('<?php echo $paper['url']; ?>', '_blank')">
                        <span class="badge badge-research">Research</span>
                        <h3><?php echo sanitize($paper['title']); ?></h3>
                        <p><strong>Authors:</strong> <?php echo sanitize($paper['authors']); ?></p>
                        <p><strong>Journal:</strong> <?php echo sanitize($paper['journal']); ?></p>
                        <div class="content-meta">
                            <div>📅 Published: <?php echo sanitize($paper['pubdate']); ?></div>
                            <div>🔗 PMID: <?php echo sanitize($paper['pmid']); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($articles)): ?>
            <div class="content-section">
                <h2>📰 Latest Articles</h2>
                <div class="content-grid">
                    <?php foreach ($articles as $article): ?>
                    <div class="content-card" onclick="window.open('<?php echo $article['url']; ?>', '_blank')">
                        <span class="badge badge-article">Article</span>
                        <h3><?php echo sanitize($article['title']); ?></h3>
                        <p><?php echo sanitize($article['excerpt']); ?></p>
                        <div class="content-meta">
                            <div>📰 Source: <?php echo sanitize($article['source']); ?></div>
                            <div>📅 <?php echo date('M d, Y', strtotime($article['created_at'])); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (empty($videos) && empty($research) && empty($articles)): ?>
            <div class="content-section" style="text-align: center; padding: 4rem;">
                <h2>No Content Yet</h2>
                <p style="font-size: 1.125rem; color: var(--text); margin-bottom: 2rem;">
                    The content scraper hasn't run yet. Run it manually to fetch the latest content.
                </p>
                <a href="scraper/advanced-scraper.php?run_advanced_scraper=1" class="btn btn-primary" style="font-size: 1.125rem; padding: 1rem 2rem;">
                    Run Scraper Now
                </a>
            </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
