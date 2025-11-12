<?php
// Endpoint: POST /api/auth/token.php
// Body: { "email": "user@example.com", "password": "secret" }

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/jwt_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if ($email === '' || $password === '') {
    jsonResponse(['success' => false, 'message' => 'Email and password are required'], 400);
}

$db = Database::getInstance();
try {
    $user = $db->fetchOne('SELECT id, password FROM users WHERE email = ?', [$email]);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Server error'], 500);
}

if (!$user || !isset($user['password']) || !password_verify($password, $user['password'])) {
    jsonResponse(['success' => false, 'message' => 'Invalid credentials'], 401);
}

$token = generate_jwt((int)$user['id']);
$payload = verify_jwt($token);

jsonResponse(['success' => true, 'token' => $token, 'expires_at' => $payload['exp'] ?? null]);
