<?php
require_once 'config/config.php';
requireLogin();

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
            
            <!-- Latest Content -->
            <section class="content-section">
                <h2>Latest Resources</h2>
                <div class="content-grid">
                    <?php foreach ($latestContent as $content): ?>
                    <div class="content-card">
                        <div class="content-type-badge"><?php echo ucfirst($content['content_type']); ?></div>
                        <h3><?php echo sanitize($content['title']); ?></h3>
                        <p><?php echo substr(sanitize($content['content']), 0, 120); ?>...</p>
                        <div class="card-footer">
                            <span class="views">👁️ <?php echo $content['views']; ?></span>
                            <a href="?id=<?php echo $content['id']; ?>" class="btn btn-link">Read More →</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            
            <!-- Content by Category -->
            <?php foreach ($contentByCategory as $category => $items): ?>
            <?php if (!empty($items)): ?>
            <section class="content-section">
                <h2><?php echo $category; ?></h2>
                <div class="content-list">
                    <?php foreach ($items as $content): ?>
                    <div class="content-list-item">
                        <div class="content-type-badge small"><?php echo ucfirst($content['content_type']); ?></div>
                        <div class="content-info">
                            <h3><a href="?id=<?php echo $content['id']; ?>"><?php echo sanitize($content['title']); ?></a></h3>
                            <p><?php echo substr(sanitize($content['content']), 0, 100); ?>...</p>
                        </div>
                        <div class="content-stats">
                            <span>👁️ <?php echo $content['views']; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
            <?php endforeach; ?>
            
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/slideshow.js"></script>
</body>
</html>
