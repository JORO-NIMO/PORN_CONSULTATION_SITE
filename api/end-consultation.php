<?php
require_once '../config/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Invalid request method'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$consultationId = isset($input['consultation_id']) ? intval($input['consultation_id']) : 0;

if (!$consultationId) {
    jsonResponse(['success' => false, 'error' => 'Consultation ID required'], 400);
}

$db = Database::getInstance();

// Verify consultation belongs to user
$consultation = $db->fetchOne(
    "SELECT * FROM consultations WHERE id = ? AND user_id = ?",
    [$consultationId, $_SESSION['user_id']]
);

if (!$consultation) {
    jsonResponse(['success' => false, 'error' => 'Consultation not found'], 404);
}

// Update consultation status
$db->query(
    "UPDATE consultations SET status = 'completed' WHERE id = ?",
    [$consultationId]
);

// Update video session end time
$db->query(
    "UPDATE video_sessions SET ended_at = datetime('now'), 
     duration_seconds = (julianday(datetime('now')) - julianday(started_at)) * 86400 
     WHERE consultation_id = ?",
    [$consultationId]
);

jsonResponse(['success' => true, 'message' => 'Consultation ended successfully']);
