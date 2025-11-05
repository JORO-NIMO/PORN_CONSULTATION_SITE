<?php
require_once 'config/config.php';
requireLogin();

$db = Database::getInstance();

// Fetch scraped psychiatrists from therapists table (by title/specialties containing 'psychiat')
$psychiatrists = $db->fetchAll(
    "SELECT id, name, title, specialties, city, country, languages, contact_email, phone, profile_url, updated_at 
     FROM therapists 
     WHERE (LOWER(title) LIKE '%psychiat%' OR LOWER(specialties) LIKE '%psychiat%')
     ORDER BY updated_at DESC"
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
                            <p class="specialization"><?php echo sanitize($psych['specialization']); ?></p>
                            <?php if (!empty($psych['city']) || !empty($psych['country'])): ?>
                            <div class="location">
                                📍 <?php echo sanitize(trim(($psych['city'] ?? '') . ' ' . ($psych['country'] ?? ''))); ?>
                            </div>
                            <?php endif; ?>
                    </div>
                </div>
                
                <div class="psychiatrist-body">
                        <?php if (!empty($psych['specialties'])): ?>
                        <div class="qualifications">
                            <h3>Specialties</h3>
                            <p><?php echo nl2br(sanitize($psych['specialties'])); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($psych['languages'])): ?>
                        <div class="languages">
                            <strong>Languages:</strong> <?php echo sanitize($psych['languages']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($psych['contact_email']) || !empty($psych['phone'])): ?>
                        <div class="contact">
                            <h3>Contact</h3>
                            <?php if (!empty($psych['contact_email'])): ?>
                                <div>Email: <a href="mailto:<?php echo sanitize($psych['contact_email']); ?>"><?php echo sanitize($psych['contact_email']); ?></a></div>
                            <?php endif; ?>
                            <?php if (!empty($psych['phone'])): ?>
                                <div>Phone: <a href="tel:<?php echo sanitize($psych['phone']); ?>"><?php echo sanitize($psych['phone']); ?></a></div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                </div>
                
                <div class="psychiatrist-footer">
                        <?php if (!empty($psych['profile_url'])): ?>
                        <a href="<?php echo sanitize($psych['profile_url']); ?>" target="_blank" rel="noopener" class="btn btn-secondary">View Source Profile</a>
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
