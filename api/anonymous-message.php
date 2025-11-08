<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/csrf_functions.php';

session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'You must be logged in to send a message.']);
    exit;
}

if (!validate_csrf_token($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token.']);
    exit;
}

$recipient_id = $_POST['recipient_id'] ?? null;
$message_content = $_POST['message'] ?? '';

if (empty($recipient_id) || empty($message_content)) {
    http_response_code(400);
    echo json_encode(['error' => 'Recipient ID and message are required.']);
    exit;
}

$sender_id = $_SESSION['user_id'];

try {
    $pdo = getPDO();

    // Verify recipient exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = :recipient_id");
    $stmt->execute(['recipient_id' => $recipient_id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Recipient not found.']);
        exit;
    }

    // Insert message into the messages table
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, recipient_id, content) VALUES (:sender_id, :recipient_id, :content)");
    $stmt->execute([
        'sender_id' => $sender_id,
        'recipient_id' => $recipient_id,
        'content' => $message_content
    ]);

    echo json_encode(['success' => 'Message sent successfully.']);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to send message.']);
}
?>