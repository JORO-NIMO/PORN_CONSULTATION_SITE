<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/jwt_middleware.php';

require_jwt();

header('Content-Type: application/json');

$user_id = $jwt_payload->user_id;
$recipient_id = $_GET['recipient_id'] ?? null;

if (!$recipient_id) {
    echo json_encode(['error' => 'Recipient ID is required.']);
    exit();
}

try {
    $pdo = getPDO();

    // Fetch messages between the current user and the recipient
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE (sender_id = :user_id AND recipient_id = :recipient_id) OR (sender_id = :recipient_id AND recipient_id = :user_id) ORDER BY timestamp ASC");
    $stmt->execute(['user_id' => $user_id, 'recipient_id' => $recipient_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $formattedMessages = [];
    foreach ($messages as $message) {
        $formattedMessages[] = [
            'id' => $message['id'],
            'content' => htmlspecialchars($message['content']),
            'timestamp' => $message['timestamp'],
            'is_sent_by_user' => ($message['sender_id'] == $user_id)
        ];
    }

    echo json_encode(['messages' => $formattedMessages]);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['error' => 'Failed to retrieve messages.']);
}
?>