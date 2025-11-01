<?php
require_once 'config/config.php';
requireLogin();

$db = Database::getInstance();

// Get consultation details
$consultationId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$consultation = $db->fetchOne(
    "SELECT c.*, p.name as psychiatrist_name, p.specialization 
     FROM consultations c 
     JOIN psychiatrists p ON c.psychiatrist_id = p.id 
     WHERE c.id = ? AND c.user_id = ?",
    [$consultationId, $_SESSION['user_id']]
);

if (!$consultation) {
    header('Location: dashboard.php');
    exit;
}

// Get or create video session
$videoSession = $db->fetchOne(
    "SELECT * FROM video_sessions WHERE consultation_id = ?",
    [$consultationId]
);

if (!$videoSession) {
    // Create new video session
    $roomId = 'room_' . uniqid();
    $userToken = generateToken();
    $psychiatristToken = generateToken();
    
    $db->query(
        "INSERT INTO video_sessions (consultation_id, room_id, user_token, psychiatrist_token) 
         VALUES (?, ?, ?, ?)",
        [$consultationId, $roomId, $userToken, $psychiatristToken]
    );
    
    $videoSession = $db->fetchOne(
        "SELECT * FROM video_sessions WHERE consultation_id = ?",
        [$consultationId]
    );
    
    // Update consultation with room ID
    $db->query(
        "UPDATE consultations SET video_room_id = ?, status = 'in_progress' WHERE id = ?",
        [$roomId, $consultationId]
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Consultation - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="video-call-page">
    <div class="video-container">
        <div class="video-header">
            <div class="consultation-info">
                <h2>Consultation with <?php echo sanitize($consultation['psychiatrist_name']); ?></h2>
                <p><?php echo sanitize($consultation['specialization']); ?></p>
            </div>
            <div class="video-controls">
                <button id="toggleVideo" class="control-btn" title="Toggle Video">📹</button>
                <button id="toggleAudio" class="control-btn" title="Toggle Audio">🎤</button>
                <button id="endCall" class="control-btn danger" title="End Call">📞</button>
            </div>
        </div>
        
        <div class="video-grid">
            <div id="remoteVideo" class="video-frame remote">
                <video id="remoteVideoElement" autoplay playsinline></video>
                <div class="video-label">Dr. <?php echo sanitize($consultation['psychiatrist_name']); ?></div>
            </div>
            <div id="localVideo" class="video-frame local">
                <video id="localVideoElement" autoplay playsinline muted></video>
                <div class="video-label">You (Anonymous)</div>
            </div>
        </div>
        
        <div class="chat-sidebar" id="chatSidebar">
            <div class="chat-header">
                <h3>Chat</h3>
                <button id="toggleChat" class="btn-close">×</button>
            </div>
            <div class="chat-messages" id="chatMessages"></div>
            <div class="chat-input">
                <input type="text" id="chatInput" placeholder="Type a message...">
                <button id="sendChat" class="btn btn-primary">Send</button>
            </div>
        </div>
        
        <button id="openChat" class="floating-chat-btn">💬</button>
    </div>
    
    <script>
        const CONFIG = {
            roomId: '<?php echo $videoSession['room_id']; ?>',
            userToken: '<?php echo $videoSession['user_token']; ?>',
            userId: <?php echo (int)$_SESSION['user_id']; ?>,
            userName: '<?php echo sanitize($_SESSION['user_name'] ?? 'You'); ?>',
            consultationId: <?php echo $consultationId; ?>,
            isAnonymous: true
        };
    </script>
    <script src="https://unpkg.com/@daily-co/daily-js"></script>
    <script src="assets/js/video-call.js"></script>
</body>
</html>
