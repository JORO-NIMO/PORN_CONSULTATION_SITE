<?php
namespace App\Core;

class App {
    /**
     * @var Router
     */
    protected $router;
    
    /**
     * @var array Application configuration
     */
    protected $config = [];
    
    /**
     * @var App Singleton instance
     */
    private static $instance = null;
    
    /**
     * Get application instance (Singleton)
     * 
     * @return App
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Load configuration
        $this->loadConfig();
        
        // Initialize router
        $this->router = new Router();
        
        // Set timezone
        date_default_timezone_set($this->config['timezone'] ?? 'UTC');
    }
    
    /**
     * Load configuration
     */
    protected function loadConfig() {
        // Load main config
        $configFile = CONFIG_PATH . '/app.php';
        
        if (file_exists($configFile)) {
            $this->config = require $configFile;
        }
    }
    
    /**
     * Get configuration value
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function config($key, $default = null) {
        return $this->config[$key] ?? $default;
    }
    
    /**
     * Get the router instance
     * 
     * @return Router
     */
    public function router() {
        return $this->router;
    }
    
    /**
     * Run the application
     */
    public function run() {
        try {
            // Handle the request
            $this->router->dispatch();
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Handle exceptions
     * 
     * @param \Exception $e
     */
    protected function handleException(\Exception $e) {
        // Log the error
        error_log($e->getMessage());
        
        // Set appropriate HTTP status code
        $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        
        // Set headers
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json');
        }
        
        // Return JSON response
        echo json_encode([
            'error' => [
                'code' => $statusCode,
                'message' => $e->getMessage(),
                'details' => ENVIRONMENT === 'development' ? $e->getTraceAsString() : null
            ]
        ]);
    }
    
    /**
     * Get database connection
     * 
     * @return \PDO
     */
    public function db() {
        static $db = null;
        
        if ($db === null) {
            // Use the unified MySQL Database instance from the root application
            // This ensures consistent connections and avoids divergence
            $rootDatabasePath = __DIR__ . '/../../../config/database.php';
            if (file_exists($rootDatabasePath)) {
                require_once $rootDatabasePath;
                $db = \Database::getInstance()->getPdo();
                // Set default fetch mode for this module to object for compatibility
                $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);
            } else {
                throw new \RuntimeException('Unified Database not found at ' . $rootDatabasePath);
            }
        }
        
        return $db;
    }
    
    /**
     * Get the current user
     * 
     * @return \stdClass|null
     */
    public function user() {
        static $user = null;
        
        if ($user === null && isset($_SESSION['user_id'])) {
            $stmt = $this->db()->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
        }
        
        return $user ?: null;
    }
    
    /**
     * Check if user is authenticated
     * 
     * @return bool
     */
    public function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Require authentication
     * 
     * @throws \RuntimeException If user is not authenticated
     */
    public function requireAuth() {
        if (!$this->isAuthenticated()) {
            if ($this->isAjax()) {
                throw new \RuntimeException('Authentication required', 401);
            }
            
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: /login');
            exit;
        }
    }
    
    /**
     * Check if request is AJAX
     * 
     * @return bool
     */
    public function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Redirect to a URL
     * 
     * @param string $url
     * @param int $statusCode
     */
    public function redirect($url, $statusCode = 302) {
        if (headers_sent()) {
            echo "<script>window.location.href='$url';</script>";
        } else {
            header("Location: $url", true, $statusCode);
        }
        exit;
    }
    
    /**
     * Get request data
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function input($key = null, $default = null) {
        $data = array_merge($_GET, $_POST);
        
        if ($key === null) {
            return $data;
        }
        
        return $data[$key] ?? $default;
    }
    
    /**
     * Get JSON request data
     * 
     * @return array
     */
    public function json() {
        static $data = null;
        
        if ($data === null) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true) ?: [];
        }
        
        return $data;
    }
    
    /**
     * Generate CSRF token
     * 
     * @return string
     */
    public function csrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     * 
     * @param string $token
     * @return bool
     */
    public function verifyCsrfToken($token) {
        return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
    
    /**
     * Generate URL
     * 
     * @param string $path
     * @param array $params
     * @return string
     */
    public function url($path = '', array $params = []) {
        $baseUrl = rtrim($this->config('app.url', ''), '/');
        $path = ltrim($path, '/');
        
        $url = $baseUrl . ($path ? "/$path" : '');
        
        if (!empty($params)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
        }
        
        return $url;
    }
    
    /**
     * Get asset URL
     * 
     * @param string $path
     * @return string
     */
    public function asset($path) {
        $baseUrl = rtrim($this->config('app.assets_url', ''), '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }
}
