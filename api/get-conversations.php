<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/jwt_middleware.php';

require_jwt();

header('Content-Type: application/json');

$user_id = $jwt_payload->user_id;

try {
    $pdo = getPDO();

    // Get unique recipients the user has messaged or been messaged by
    $stmt = $pdo->prepare("SELECT DISTINCT 
                                CASE 
                                    WHEN sender_id = :user_id THEN recipient_id 
                                    ELSE sender_id 
                                END AS participant_id
                            FROM messages 
                            WHERE sender_id = :user_id OR recipient_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $participants = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $conversations = [];
    foreach ($participants as $participant_id) {
        // Get the last message for each conversation
        $stmt = $pdo->prepare("SELECT content, timestamp, sender_id 
                                FROM messages 
                                WHERE (sender_id = :user_id AND recipient_id = :participant_id) 
                                   OR (sender_id = :participant_id AND recipient_id = :user_id) 
                                ORDER BY timestamp DESC 
                                LIMIT 1");
        $stmt->execute(['user_id' => $user_id, 'participant_id' => $participant_id]);
        $last_message = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get participant's username
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = :participant_id");
        $stmt->execute(['participant_id' => $participant_id]);
        $participant_username = $stmt->fetchColumn();

        if ($last_message && $participant_username) {
            $conversations[] = [
                'recipient_id' => $participant_id,
                'recipient_username' => htmlspecialchars($participant_username),
                'last_message_snippet' => htmlspecialchars(substr($last_message['content'], 0, 50)) . (strlen($last_message['content']) > 50 ? '...' : ''),
                'timestamp' => $last_message['timestamp'],
                'is_last_message_from_user' => ($last_message['sender_id'] == $user_id)
            ];
        }
    }

    // Sort conversations by latest message
    usort($conversations, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });

    echo json_encode(['conversations' => $conversations]);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['error' => 'Failed to retrieve conversations.']);
}
?>