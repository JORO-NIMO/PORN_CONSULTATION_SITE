<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/mail_helper.php';

// Enforce POST and CSRF for security
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

header('Content-Type: application/json');

$name = sanitize($_POST['name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$subject = sanitize($_POST['subject'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$company = sanitize($_POST['company'] ?? '');
$message = trim($_POST['message'] ?? '');
$token = $_POST['csrf_token'] ?? '';

if (!validateCSRFToken($token)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$message) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Please provide name, valid email, and message']);
    exit;
}

// Save to database
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, phone, company, message) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $email, $subject, $phone, $company, $message]);
} catch (PDOException $e) {
    error_log("Contact form DB error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Could not save your message. Please try again later.']);
    exit;
}

// Send to admin
if (!defined('ADMIN_EMAIL') || !filter_var(ADMIN_EMAIL, FILTER_VALIDATE_EMAIL)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Admin email not configured']);
    exit;
}

$siteName = defined('SITE_NAME') ? SITE_NAME : 'Website';
$emailSubject = '[' . $siteName . '] New contact message';
if (!empty($subject)) {
    $emailSubject .= ': ' . htmlspecialchars($subject);
}

$html = '<p>New contact message</p>' .
        '<ul>' .
        '<li>Name: ' . htmlspecialchars($name) . '</li>' .
        '<li>Email: ' . htmlspecialchars($email) . '</li>' .
        (!empty($phone) ? '<li>Phone: ' . htmlspecialchars($phone) . '</li>' : '') .
        (!empty($company) ? '<li>Company: ' . htmlspecialchars($company) . '</li>' : '') .
        '</ul>' .
        '<p>Message:</p><pre style="white-space:pre-wrap">' . htmlspecialchars($message) . '</pre>';

$ok = send_mail_safe(ADMIN_EMAIL, $emailSubject, $html, strip_tags($html), $email);
echo json_encode(['success' => (bool)$ok]);