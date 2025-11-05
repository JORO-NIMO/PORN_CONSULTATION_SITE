<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/csrf.php';

session_start();

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

$recipient_username = $_POST['recipient_username'] ?? '';
$message = $_POST['message'] ?? '';

if (empty($recipient_username) || empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Recipient and message are required.']);
    exit;
}

$db = Database::getInstance();

// Get recipient ID
$recipient = $db->fetchOne('SELECT id FROM users WHERE username = ?', [$recipient_username]);

if (!$recipient) {
    http_response_code(404);
    echo json_encode(['error' => 'Recipient not found.']);
    exit;
}

$recipient_id = $recipient['id'];
$sender_id = $_SESSION['user_id'];

// Insert message into the database
$db->query('INSERT INTO anonymous_messages (sender_id, recipient_id, message) VALUES (?, ?, ?)', [$sender_id, $recipient_id, $message]);

// For now, we'll just confirm the message was sent.
// In a real application, you might send an email or notification.

http_response_code(200);
echo json_encode(['success' => 'Message sent successfully.']);