<?php
// REST API for therapists directory
// Routes:
//  GET  /api/therapists           (filters: city, country, specialty, language)
//  GET  /api/therapists/{id}
//  POST /api/therapists/claim     (therapist_id, email)
//  PUT  /api/therapists/{id}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { echo json_encode(['ok' => true]); exit; }

require_once __DIR__ . '/../includes/therapist_db.php';
require_once __DIR__ . '/../includes/claim_mailer.php';

$db = new TherapistDB();
$pdo = $db->pdo();

function jsonResponse($data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Parse route
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?? '/';
$segments = array_values(array_filter(explode('/', $path)));

// Expect segments starting with ['api','therapists', ...]
if (count($segments) < 2 || $segments[0] !== 'api' || $segments[1] !== 'therapists') {
    jsonResponse(['error' => 'Not Found'], 404);
}

$method = $_SERVER['REQUEST_METHOD'];
$id = null;
$action = null;

if (isset($segments[2])) {
    if (is_numeric($segments[2])) { $id = (int)$segments[2]; }
    else { $action = $segments[2]; }
}

try {
    if ($method === 'GET' && !$id && !$action) {
        // List therapists with optional filters
        $q = 'SELECT id, source, source_id, name, title, specialties, city, country, languages, contact_email, phone, profile_url, verified, last_scraped, updated_at FROM therapists WHERE 1=1';
        $params = [];
        if (!empty($_GET['city'])) { $q .= ' AND city LIKE ?'; $params[] = '%' . $_GET['city'] . '%'; }
        if (!empty($_GET['country'])) { $q .= ' AND country LIKE ?'; $params[] = '%' . $_GET['country'] . '%'; }
        if (!empty($_GET['specialty'])) { $q .= ' AND specialties LIKE ?'; $params[] = '%' . $_GET['specialty'] . '%'; }
        if (!empty($_GET['language'])) { $q .= ' AND languages LIKE ?'; $params[] = '%' . $_GET['language'] . '%'; }
        $q .= ' ORDER BY updated_at DESC LIMIT 500';
        $stmt = $pdo->prepare($q);
        $stmt->execute($params);
        jsonResponse(['data' => $stmt->fetchAll()]);
    }
    
    if ($method === 'GET' && $id) {
        $stmt = $pdo->prepare('SELECT * FROM therapists WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) jsonResponse(['error' => 'Not Found'], 404);
        jsonResponse(['data' => $row]);
    }

    if ($method === 'POST' && $action === 'claim') {
        $input = json_decode(file_get_contents('php://input'), true);
        $therapistId = (int)($input['therapist_id'] ?? 0);
        $email = trim($input['email'] ?? '');
        if ($therapistId <= 0 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['error' => 'Invalid therapist_id or email'], 422);
        }
        // Create token
        $token = bin2hex(random_bytes(24));
        $expires = date('Y-m-d H:i:s', time() + 60*60*24); // 24h
        $stmt = $pdo->prepare('INSERT INTO therapist_claims (therapist_id, claim_email, token, expires_at) VALUES (?, ?, ?, ?)');
        $stmt->execute([$therapistId, $email, $token, $expires]);
        // Build absolute verification URL
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $verifyUrl = $scheme . '://' . $host . '/api/therapists/verify?token=' . urlencode($token);
        // Send email via PHPMailer using existing mail settings
        $ok = send_claim_email($email, $verifyUrl, ['therapist_name' => $input['name'] ?? '']);
        jsonResponse(['ok' => $ok, 'verify_url' => $verifyUrl]);
    }

    if ($method === 'GET' && $action === 'verify') {
        $token = $_GET['token'] ?? '';
        if (!$token) jsonResponse(['error' => 'Missing token'], 422);
        $stmt = $pdo->prepare('SELECT * FROM therapist_claims WHERE token = ? AND used_at IS NULL AND expires_at > NOW()');
        $stmt->execute([$token]);
        $claim = $stmt->fetch();
        if (!$claim) jsonResponse(['error' => 'Invalid or expired token'], 400);
        // Mark therapist as verified
        $pdo->prepare('UPDATE therapists SET verified = 1 WHERE id = ?')->execute([$claim['therapist_id']]);
        $pdo->prepare('UPDATE therapist_claims SET used_at = NOW() WHERE id = ?')->execute([$claim['id']]);
        // Minimal session-like cookie so edit page can allow update
        setcookie('therapist_id', (string)$claim['therapist_id'], time()+60*60*24*7, '/');
        jsonResponse(['ok' => true, 'therapist_id' => (int)$claim['therapist_id']]);
    }

    if ($method === 'PUT' && $id) {
        $input = json_decode(file_get_contents('php://input'), true);
        // Basic auth: must be verified and match cookie
        $cookieId = isset($_COOKIE['therapist_id']) ? (int)$_COOKIE['therapist_id'] : 0;
        if ($cookieId !== $id) jsonResponse(['error' => 'Unauthorized'], 401);
        $isVerified = (int)$pdo->query('SELECT verified FROM therapists WHERE id=' . (int)$id)->fetchColumn();
        if ($isVerified !== 1) jsonResponse(['error' => 'Not verified'], 403);
        // Allow updates to specific fields
        $fields = ['name','title','specialties','city','country','languages','contact_email','phone'];
        $sets = []; $params = [];
        foreach ($fields as $f) {
            if (isset($input[$f])) { $sets[] = "$f = ?"; $params[] = trim((string)$input[$f]); }
        }
        if (!$sets) jsonResponse(['error' => 'No fields to update'], 422);
        $params[] = $id;
        $sql = 'UPDATE therapists SET ' . implode(', ', $sets) . ', updated_at = NOW() WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        jsonResponse(['ok' => true]);
    }

    jsonResponse(['error' => 'Method Not Allowed'], 405);
} catch (Throwable $e) {
    jsonResponse(['error' => 'Server Error', 'message' => $e->getMessage()], 500);
}