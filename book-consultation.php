<?php
require_once 'config/config.php';
requireLogin();

$db = Database::getInstance();

// Get psychiatrist details
$psychiatristId = isset($_GET['psychiatrist']) ? intval($_GET['psychiatrist']) : 0;
$psychiatrist = $db->fetchOne(
    "SELECT * FROM psychiatrists WHERE id = ? AND is_active = 1",
    [$psychiatristId]
);

if (!$psychiatrist) {
    header('Location: psychiatrists.php');
    exit;
}

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scheduledTime = sanitize($_POST['scheduled_time'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    
    if (!empty($scheduledTime)) {
        // Create consultation
        $db->query(
            "INSERT INTO consultations (user_id, psychiatrist_id, scheduled_time, notes) 
             VALUES (?, ?, ?, ?)",
            [$_SESSION['user_id'], $psychiatristId, $scheduledTime, $notes]
        );
        
        $consultationId = $db->lastInsertId();
        
        // Create video room
        $roomId = 'room_' . uniqid();
        $userToken = generateToken();
        $psychiatristToken = generateToken();
        
        $db->query(
            "INSERT INTO video_sessions (consultation_id, room_id, user_token, psychiatrist_token) 
             VALUES (?, ?, ?, ?)",
            [$consultationId, $roomId, $userToken, $psychiatristToken]
        );
        
        $db->query(
            "UPDATE consultations SET video_room_id = ? WHERE id = ?",
            [$roomId, $consultationId]
        );
        
        if (isAjax()) {
            jsonResponse(['success' => true, 'consultation_id' => $consultationId]);
        }
        
        header('Location: dashboard.php?booked=1');
        exit;
    }
}

// Get psychiatrist's availability
$availability = json_decode($psychiatrist['availability'], true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Consultation - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="booking-page" style="background: var(--light); min-height: 100vh; padding: 2rem 0;">
        <div class="container">
            <div class="page-header">
                <a href="psychiatrists.php" class="back-link" style="color: var(--primary); text-decoration: none; margin-bottom: 1rem; display: inline-block;">← Back to Psychiatrists</a>
                <h1>Book Consultation</h1>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 2rem;">
                <!-- Psychiatrist Info -->
                <div style="background: white; border-radius: 16px; padding: 2rem; box-shadow: var(--shadow); height: fit-content;">
                    <div class="psychiatrist-header">
                        <div class="psychiatrist-avatar">
                            <?php if ($psychiatrist['profile_image']): ?>
                                <img src="<?php echo sanitize($psychiatrist['profile_image']); ?>" alt="<?php echo sanitize($psychiatrist['name']); ?>">
                            <?php else: ?>
                                <div class="avatar-placeholder">👨‍⚕️</div>
                            <?php endif; ?>
                        </div>
                        <div class="psychiatrist-info">
                            <h2><?php echo sanitize($psychiatrist['name']); ?></h2>
                            <p class="specialization"><?php echo sanitize($psychiatrist['specialization']); ?></p>
                            <div class="rating">
                                ⭐ <?php echo number_format($psychiatrist['rating'], 2); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 1.5rem;">
                        <h3 style="margin-bottom: 0.5rem;">Experience</h3>
                        <p><?php echo $psychiatrist['experience_years']; ?> years</p>
                        
                        <h3 style="margin-top: 1rem; margin-bottom: 0.5rem;">Availability</h3>
                        <div class="availability-schedule">
                            <?php foreach ($availability as $day => $hours): ?>
                            <div class="schedule-item" style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--border);">
                                <span class="day" style="font-weight: 600;"><?php echo ucfirst($day); ?>:</span>
                                <span class="hours"><?php echo $hours; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Booking Form -->
                <div style="background: white; border-radius: 16px; padding: 2rem; box-shadow: var(--shadow);">
                    <h2 style="margin-bottom: 1.5rem;">Schedule Your Consultation</h2>
                    
                    <form method="POST" class="booking-form">
                        <div class="form-group">
                            <label for="scheduled_time">Preferred Date & Time</label>
                            <input type="datetime-local" id="scheduled_time" name="scheduled_time" required 
                                   min="<?php echo date('Y-m-d\TH:i'); ?>">
                            <small class="field-help">Please select a time within the psychiatrist's availability hours</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Notes (Optional)</label>
                            <textarea id="notes" name="notes" rows="5" 
                                placeholder="Share any specific concerns or topics you'd like to discuss..."></textarea>
                            <small class="field-help">This information will help the psychiatrist prepare for your session</small>
                        </div>
                        
                        <div style="background: #eff6ff; border-left: 4px solid var(--primary); padding: 1rem; margin: 1.5rem 0; border-radius: 8px;">
                            <h4 style="margin-bottom: 0.5rem;">🔒 Privacy & Anonymity</h4>
                            <ul style="margin-left: 1.5rem; color: var(--text-light);">
                                <li>Your consultation will be completely confidential</li>
                                <li>Video calls are anonymous - you can choose to hide your identity</li>
                                <li>All communications are encrypted</li>
                                <li>No recordings are made without your consent</li>
                            </ul>
                        </div>
                        
                        <div style="background: #fef3c7; border-left: 4px solid var(--warning); padding: 1rem; margin: 1.5rem 0; border-radius: 8px;">
                            <h4 style="margin-bottom: 0.5rem;">⏰ Consultation Details</h4>
                            <ul style="margin-left: 1.5rem; color: var(--text);">
                                <li>Duration: 60 minutes</li>
                                <li>Format: Secure video call</li>
                                <li>You'll receive a reminder 24 hours before</li>
                                <li>Cancellations accepted up to 12 hours before</li>
                            </ul>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1.5rem;">
                            Confirm Booking
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
