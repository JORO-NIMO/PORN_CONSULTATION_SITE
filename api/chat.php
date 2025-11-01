<?php
// Load configuration
require_once __DIR__ . '/../config.php';

// Set headers
header('Content-Type: application/json');
// Tighten CORS: allow only the configured BASE_URL origin
$allowedOrigin = defined('BASE_URL') ? (parse_url(BASE_URL, PHP_URL_SCHEME) . '://' . parse_url(BASE_URL, PHP_URL_HOST)) : 'http://localhost';
$requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if ($requestOrigin && $requestOrigin === $allowedOrigin) {
        header("Access-Control-Allow-Origin: {$allowedOrigin}");
    }
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    http_response_code(200);
    exit();
}
if ($requestOrigin && $requestOrigin === $allowedOrigin) {
    header("Access-Control-Allow-Origin: {$allowedOrigin}");
} else if ($requestOrigin) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Origin not allowed']);
    exit();
}

// Include the service
require_once __DIR__ . '/../app/services/GeminiWellnessService.php';

// Get API key from config
$apiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';

if (empty($apiKey) || $apiKey === 'YOUR_GEMINI_API_KEY') {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'API key not configured. Please check your config.php file.'
    ]);
    exit();
}

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$message = $input['message'] ?? '';
$context = $input['context'] ?? [];

if (empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'No message provided']);
    exit();
}

try {
    $service = new GeminiWellnessService($apiKey);
    $response = $service->getWellnessResponse($message, implode("\n", $context));
    
    echo json_encode([
        'success' => true,
        'response' => $response['response'],
        'is_crisis' => $response['is_crisis']
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while processing your request',
        'details' => $e->getMessage()
    ]);
}
