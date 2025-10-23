<?php
require_once 'config/config.php';
requireLogin();

$db = Database::getInstance();

// Get user stats
$messageCount = $db->fetchOne("SELECT COUNT(*) as count FROM messages WHERE sender_id = ?", [$_SESSION['user_id']])['count'];
$consultationCount = $db->fetchOne("SELECT COUNT(*) as count FROM consultations WHERE user_id = ?", [$_SESSION['user_id']])['count'];

// Get featured educational content
$featuredContent = $db->fetchAll("SELECT * FROM educational_content WHERE is_featured = 1 ORDER BY created_at DESC LIMIT 3");

// Get upcoming consultations
$upcomingConsultations = $db->fetchAll(
    "SELECT c.*, p.name as psychiatrist_name, p.specialization 
     FROM consultations c 
     JOIN psychiatrists p ON c.psychiatrist_id = p.id 
     WHERE c.user_id = ? AND c.status = 'scheduled' AND c.scheduled_time > datetime('now') 
     ORDER BY c.scheduled_time ASC LIMIT 3",
    [$_SESSION['user_id']]
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <section class="page-hero">
        <div class="container">
            <h1>Dashboard</h1>
            <p class="subtitle">Your progress and quick tools</p>
        </div>
    </section>
    <main class="dashboard">
        <div class="container">
            <div class="welcome-section">
                <h1>Welcome back, <?php echo sanitize($_SESSION['user_name']); ?>!</h1>
                <p class="subtitle">Your journey to freedom continues here</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">💬</div>
                    <div class="stat-content">
                        <h3><?php echo $messageCount; ?></h3>
                        <p>Messages Sent</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👨‍⚕️</div>
                    <div class="stat-content">
                        <h3><?php echo $consultationCount; ?></h3>
                        <p>Consultations</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-content">
                        <h3><?php echo count($featuredContent); ?></h3>
                        <p>Resources Available</p>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($upcomingConsultations)): ?>
            <section class="dashboard-section">
                <h2>Upcoming Consultations</h2>
                <div class="consultations-list">
                    <?php foreach ($upcomingConsultations as $consult): ?>
                    <div class="consultation-card">
                        <div class="consultation-info">
                            <h3><?php echo sanitize($consult['psychiatrist_name']); ?></h3>
                            <p class="specialization"><?php echo sanitize($consult['specialization']); ?></p>
                            <p class="datetime">
                                📅 <?php echo date('M d, Y', strtotime($consult['scheduled_time'])); ?> 
                                at <?php echo date('h:i A', strtotime($consult['scheduled_time'])); ?>
                            </p>
                        </div>
                        <div class="consultation-actions">
                            <?php if ($consult['video_room_id']): ?>
                            <a href="video-call.php?id=<?php echo $consult['id']; ?>" class="btn btn-primary">Join Call</a>
                            <?php endif; ?>
                            <a href="consultations.php?id=<?php echo $consult['id']; ?>" class="btn btn-secondary">Details</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
            
            <section class="dashboard-section">
                <h2>Featured Educational Content</h2>
                <div class="content-grid">
                    <?php foreach ($featuredContent as $content): ?>
                    <div class="content-card">
                        <div class="content-type-badge"><?php echo ucfirst($content['content_type']); ?></div>
                        <h3><?php echo sanitize($content['title']); ?></h3>
                        <p><?php echo substr(sanitize($content['content']), 0, 150); ?>...</p>
                        <a href="education.php?id=<?php echo $content['id']; ?>" class="btn btn-link">Read More →</a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            
            <section class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="actions-grid">
                    <a href="psychiatrists.php" class="action-card">
                        <span class="action-icon">👨‍⚕️</span>
                        <h3>Find a Psychiatrist</h3>
                        <p>Browse our directory of specialists</p>
                    </a>
                    <a href="messages.php" class="action-card">
                        <span class="action-icon">💬</span>
                        <h3>Anonymous Messaging</h3>
                        <p>Reach out for support</p>
                    </a>
                    <a href="education.php" class="action-card">
                        <span class="action-icon">📚</span>
                        <h3>Educational Resources</h3>
                        <p>Learn about recovery</p>
                    </a>
                    
                </div>
            </section>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
