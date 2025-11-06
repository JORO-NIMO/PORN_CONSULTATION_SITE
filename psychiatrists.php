<?php
require_once 'config/config.php';
requireLogin();

$db = Database::getInstance();

// Fetch psychiatrists from the dedicated psychiatrists table
$psychiatrists = $db->fetchAll(
    "SELECT id, name, specialization, email, profile_image, rating, total_consultations, created_at
     FROM psychiatrists
     WHERE is_active = 1
     ORDER BY created_at DESC"
);
// Deduplicate by name (case-insensitive, trimmed)
$seen = [];
$uniquePsychiatrists = [];
foreach ($psychiatrists as $p) {
    $key = strtolower(trim($p['name'] ?? ''));
    if ($key === '') { $key = (string)($p['id'] ?? uniqid('psy_')); }
    if (isset($seen[$key])) { continue; }
    $seen[$key] = true;
    $uniquePsychiatrists[] = $p;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Psychiatrists Directory - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <section class="page-hero">
        <div class="container">
            <h1>Our Psychiatrists</h1>
            <p class="subtitle">Connect with experienced professionals who understand your journey</p>
        </div>
    </section>
    <main class="psychiatrists-page">
        <div class="container">
            
            <div class="psychiatrists-grid">
                <?php foreach ($uniquePsychiatrists as $psych): ?>
                <div class="psychiatrist-card">
                    <div class="psychiatrist-header">
                        <div class="psychiatrist-avatar">
                            <?php if ($psych['profile_image']): ?>
                                <img src="<?php echo sanitize($psych['profile_image']); ?>" alt="<?php echo sanitize($psych['name']); ?>">
                            <?php else: ?>
                                <div class="avatar-placeholder">👨‍⚕️</div>
                            <?php endif; ?>
                        </div>
                        <div class="psychiatrist-info">
                            <h2><?php echo sanitize($psych['name']); ?></h2>
                            <p class="specialization"><?php echo sanitize($psych['specialization'] ?? ''); ?></p>
                            <?php if (!empty($psych['city']) || !empty($psych['country'])): ?>
                            <div class="location">
                                📍 <?php echo sanitize(trim(($psych['city'] ?? '') . ' ' . ($psych['country'] ?? ''))); ?>
                            </div>
                            <?php endif; ?>
                    </div>
                </div>
                
                <div class="psychiatrist-body">
                        <div class="contact">
                            <h3>Contact</h3>
                            <?php if (!empty($psych['email'])): ?>
                                <div>Email: <a href="mailto:<?php echo sanitize($psych['email']); ?>"><?php echo sanitize($psych['email']); ?></a></div>
                            <?php endif; ?>
                            <?php if (!empty($psych['rating'])): ?>
                                <div>Rating: <?php echo sanitize($psych['rating']); ?> ⭐</div>
                            <?php endif; ?>
                            <?php if (!empty($psych['total_consultations'])): ?>
                                <div>Total Consultations: <?php echo intval($psych['total_consultations']); ?></div>
                            <?php endif; ?>
                        </div>
                </div>
                
                <div class="psychiatrist-footer">
                        <?php if (!empty($psych['email'])): ?>
                        <a href="mailto:<?php echo sanitize($psych['email']); ?>" class="btn btn-secondary">Email Psychiatrist</a>
                        <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
