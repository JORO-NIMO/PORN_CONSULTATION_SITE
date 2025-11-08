<?php
namespace App\Core;

use App\Core\View;
use App\Core\Session;
use App\Core\Request;
use App\Core\Auth;

class Controller {
    /**
     * @var View
     */
    protected $view;
    
    /**
     * @var Session
     */
    protected $session;
    
    /**
     * @var Request
     */
    protected $request;
    
    /**
     * @var Auth
     */
    protected $auth;
    
    /**
     * Controller constructor
     */
    public function __construct() {
        $this->view = new View();
        $this->session = Session::getInstance();
        $this->request = new Request();
        $this->auth = new Auth();
    }
    
    /**
     * Render a view
     * 
     * @param string $view
     * @param array $data
     * @return void
     */
    protected function view($view, $data = []) {
        $this->view->render($view, $data);
    }
    
    /**
     * Redirect to a URL
     * 
     * @param string $url
     * @param int $statusCode
     * @return void
     */
    protected function redirect($url, $statusCode = 302) {
        if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
            $url = url($url);
        }
        
        header('Location: ' . $url, true, $statusCode);
        exit;
    }
    
    /**
     * Redirect back to the previous page
     * 
     * @return void
     */
    protected function redirectBack() {
        $referer = $this->request->server('HTTP_REFERER') ?? '/';
        $this->redirect($referer);
    }
    
    /**
     * Generate CSRF token
     * 
     * @return string
     */
    protected function generateCsrfToken() {
        if (empty($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['_token'];
    }
    
    /**
     * Validate CSRF token
     * 
     * @return bool
     * @throws \Exception
     */
    protected function validateCsrfToken() {
        $token = $this->request->post('_token') ?? $this->request->header('X-CSRF-TOKEN');
        
        if (empty($token) || !hash_equals($_SESSION['_token'] ?? '', $token)) {
            throw new \Exception('CSRF token validation failed');
        }
        
        return true;
    }
    
    /**
     * Return JSON response
     * 
     * @param mixed $data
     * @param int $statusCode
     * @return void
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Check if request is AJAX
     * 
     * @return bool
     */
    protected function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Require authentication
     * 
     * @return void
     */
    protected function requireAuth() {
        if (!$this->auth->check()) {
            if ($this->isAjax()) {
                $this->json(['error' => 'Unauthenticated'], 401);
            }
            
            $this->session->set('redirect_after_login', $this->request->uri());
            $this->redirect('/login');
        }
    }
    
    /**
     * Require admin role
     * 
     * @return void
     */
    protected function requireAdmin() {
        $this->requireAuth();
    }
}
