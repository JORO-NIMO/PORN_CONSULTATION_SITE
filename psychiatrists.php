<?php
require_once 'config/config.php';
requireLogin();

$db = Database::getInstance();

// Get all active psychiatrists
$psychiatrists = $db->fetchAll(
    "SELECT * FROM psychiatrists WHERE is_active = 1 ORDER BY rating DESC, total_consultations DESC"
);
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
    
    <main class="psychiatrists-page">
        <div class="container">
            <div class="page-header">
                <h1>Our Psychiatrists</h1>
                <p class="subtitle">Connect with experienced professionals who understand your journey</p>
            </div>
            
            <div class="psychiatrists-grid">
                <?php foreach ($psychiatrists as $psych): ?>
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
                            <div class="rating">
                                ⭐ <?php echo number_format($psych['rating'], 2); ?> 
                                <span class="consultations-count">(<?php echo $psych['total_consultations']; ?> consultations)</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="psychiatrist-body">
                        <div class="bio">
                            <h3>About</h3>
                            <p><?php echo nl2br(sanitize($psych['bio'])); ?></p>
                        </div>
                        
                        <div class="qualifications">
                            <h3>Qualifications</h3>
                            <p><?php echo nl2br(sanitize($psych['qualifications'])); ?></p>
                        </div>
                        
                        <div class="experience">
                            <strong>Experience:</strong> <?php echo $psych['experience_years']; ?> years
                        </div>
                        
                        <?php if ($psych['availability']): ?>
                        <div class="availability">
                            <h3>Availability</h3>
                            <div class="availability-schedule">
                                <?php 
                                $availability = json_decode($psych['availability'], true);
                                foreach ($availability as $day => $hours): 
                                ?>
                                <div class="schedule-item">
                                    <span class="day"><?php echo ucfirst($day); ?>:</span>
                                    <span class="hours"><?php echo $hours; ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="psychiatrist-footer">
                        <a href="book-consultation.php?psychiatrist=<?php echo $psych['id']; ?>" class="btn btn-primary">Book Consultation</a>
                        <a href="messages.php?to=psychiatrist&id=<?php echo $psych['id']; ?>" class="btn btn-secondary">Send Message</a>
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
