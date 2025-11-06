<?php
require_once 'config/config.php';
requireLogin();

$db = Database::getInstance();

// Handle new message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $recipientId = isset($_POST['recipient_id']) ? intval($_POST['recipient_id']) : null;
    $psychiatristId = isset($_POST['psychiatrist_id']) ? intval($_POST['psychiatrist_id']) : null;
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    $isAnonymous = isset($_POST['is_anonymous']) ? 1 : 0;
    
    if (!empty($message)) {
        $db->query(
            "INSERT INTO messages (sender_id, recipient_id, psychiatrist_id, subject, message, is_anonymous) 
             VALUES (?, ?, ?, ?, ?, ?)",
            [$_SESSION['user_id'], $recipientId, $psychiatristId, $subject, $message, $isAnonymous]
        );
        
        if (isAjax()) {
            jsonResponse(['success' => true, 'message' => 'Message sent successfully']);
        }
        header('Location: messages.php?sent=1');
        exit;
    }
}

// Get messages
$sentMessages = $db->fetchAll(
    "SELECT m.*, p.name as psychiatrist_name 
     FROM messages m 
     LEFT JOIN psychiatrists p ON m.psychiatrist_id = p.id 
     WHERE m.sender_id = ? 
     ORDER BY m.created_at DESC",
    [$_SESSION['user_id']]
);

$receivedMessages = $db->fetchAll(
    "SELECT m.*, 
            COALESCE(
                NULLIF(CONCAT_WS(' ', u.first_name, u.last_name), ''),
                NULLIF(u.username, ''),
                u.email
            ) AS sender_name, 
            p.name as psychiatrist_name 
     FROM messages m 
     LEFT JOIN users u ON m.sender_id = u.id 
     LEFT JOIN psychiatrists p ON m.psychiatrist_id = p.id 
     WHERE m.recipient_id = ? OR m.psychiatrist_id IN (SELECT id FROM psychiatrists WHERE email = ?) 
     ORDER BY m.created_at DESC",
    [$_SESSION['user_id'], $_SESSION['user_email']]
);

// Get psychiatrists for messaging
$psychiatrists = $db->fetchAll("SELECT id, name, specialization FROM psychiatrists WHERE is_active = 1");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anonymous Messaging - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="messages-page">
        <div class="container">
            <div class="page-header">
                <h1>Anonymous Messaging</h1>
                <p class="subtitle">Reach out for support in a safe, confidential space</p>
            </div>
            
            <?php if (isset($_GET['sent'])): ?>
            <div class="alert success">Message sent successfully!</div>
            <?php endif; ?>
            
            <div class="messages-layout">
                <div class="compose-section">
                    <h2>Send a Message</h2>
                    <form id="messageForm" method="POST" class="message-form">
                        <input type="hidden" name="send_message" value="1">
                        
                        <div class="form-group">
                            <label for="psychiatrist_id">Send to Psychiatrist</label>
                            <select id="psychiatrist_id" name="psychiatrist_id" required>
                                <option value="">-- Select Psychiatrist --</option>
                                <?php foreach ($psychiatrists as $psych): ?>
                                <option value="<?php echo $psych['id']; ?>" 
                                    <?php echo (isset($_GET['id']) && $_GET['id'] == $psych['id']) ? 'selected' : ''; ?>>
                                    <?php echo sanitize($psych['name']); ?> - <?php echo sanitize($psych['specialization']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" placeholder="Optional">
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Your Message</label>
                            <textarea id="message" name="message" rows="6" required 
                                placeholder="Share your thoughts, questions, or concerns..."></textarea>
                        </div>
                        
                        <div class="form-group checkbox-group">
                            <label>
                                <input type="checkbox" name="is_anonymous" checked>
                                Send anonymously (your name will be hidden)
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
                
                <div class="messages-section">
                    <div class="messages-tabs">
                        <button class="tab-btn active" data-tab="received">Received (<?php echo count($receivedMessages); ?>)</button>
                        <button class="tab-btn" data-tab="sent">Sent (<?php echo count($sentMessages); ?>)</button>
                    </div>
                    
                    <div class="tab-content active" id="received-tab">
                        <?php if (empty($receivedMessages)): ?>
                        <p class="no-messages">No messages received yet</p>
                        <?php else: ?>
                        <div class="messages-list">
                            <?php foreach ($receivedMessages as $msg): ?>
                            <div class="message-item <?php echo $msg['is_read'] ? '' : 'unread'; ?>">
                                <div class="message-header">
                                    <strong>
                                        <?php echo $msg['is_anonymous'] ? 'Anonymous User' : sanitize($msg['sender_name'] ?? 'Unknown'); ?>
                                    </strong>
                                    <span class="message-date"><?php echo date('M d, Y h:i A', strtotime($msg['created_at'])); ?></span>
                                </div>
                                <?php if ($msg['subject']): ?>
                                <div class="message-subject"><?php echo sanitize($msg['subject']); ?></div>
                                <?php endif; ?>
                                <div class="message-body"><?php echo nl2br(sanitize($msg['message'])); ?></div>
                                <div class="message-actions">
                                    <a href="?reply=<?php echo $msg['id']; ?>" class="btn btn-sm btn-secondary">Reply</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="tab-content" id="sent-tab">
                        <?php if (empty($sentMessages)): ?>
                        <p class="no-messages">No messages sent yet</p>
                        <?php else: ?>
                        <div class="messages-list">
                            <?php foreach ($sentMessages as $msg): ?>
                            <div class="message-item">
                                <div class="message-header">
                                    <strong>To: <?php echo sanitize($msg['psychiatrist_name'] ?? 'Unknown'); ?></strong>
                                    <span class="message-date"><?php echo date('M d, Y h:i A', strtotime($msg['created_at'])); ?></span>
                                </div>
                                <?php if ($msg['subject']): ?>
                                <div class="message-subject"><?php echo sanitize($msg['subject']); ?></div>
                                <?php endif; ?>
                                <div class="message-body"><?php echo nl2br(sanitize($msg['message'])); ?></div>
                                <?php if ($msg['is_anonymous']): ?>
                                <div class="message-badge">Sent anonymously</div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/messages.js"></script>
</body>
</html>
