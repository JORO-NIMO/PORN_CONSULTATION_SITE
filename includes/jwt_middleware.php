<?php
require_once __DIR__ . '/jwt_helper.php';

if (!function_exists('get_authorization_bearer_token')) {
    function get_authorization_bearer_token(): ?string {
        // Try several server variables to support different environments
        $headers = [];
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        }
        $auth = null;
        if (!empty($headers['Authorization'])) $auth = $headers['Authorization'];
        if (!$auth && !empty($_SERVER['HTTP_AUTHORIZATION'])) $auth = $_SERVER['HTTP_AUTHORIZATION'];
        if (!$auth && !empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) $auth = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];

        if (!$auth) return null;
        if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }
}

if (!function_exists('require_jwt')) {
    function require_jwt() {
        // Accept token via Authorization: Bearer <token> or access_token GET/POST param
        $token = get_authorization_bearer_token();
        if (!$token) {
            $token = $_GET['access_token'] ?? $_POST['access_token'] ?? null;
        }
        if (!$token) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Missing token']);
            exit;
        }

        $payload = verify_jwt($token);
        if (!$payload) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
            exit;
        }

        // Provide user id and payload to the application
        $_SERVER['jwt_payload'] = $payload;
        $_SERVER['jwt_user_id'] = $payload['sub'] ?? null;
        return $payload;
    }
}
