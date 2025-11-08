<?php
namespace App\Core;

use App\Models\User;

class Auth {
    /**
     * @var User
     */
    protected $user;
    
    /**
     * Check if user is authenticated
     * 
     * @return bool
     */
    public function check() {
        return $this->user() !== null;
    }
    
    /**
     * Get the authenticated user
     * 
     * @return User|null
     */
    public function user() {
        if ($this->user !== null) {
            return $this->user;
        }
        
        $userId = Session::get('user_id');
        
        if ($userId) {
            $userModel = new User();
            $this->user = $userModel->find($userId);
            
            if ($this->user) {
                return $this->user;
            }
        }
        
        // Check remember me token
        $rememberToken = Cookie::get('remember_token');
        
        if ($rememberToken) {
            return $this->attemptRememberLogin($rememberToken);
        }
        
        return null;
    }
    
    /**
     * Attempt to log in a user
     * 
     * @param string $email
     * @param string $password
     * @param bool $remember
     * @return bool
     */
    public function attempt($email, $password, $remember = false) {
        $userModel = new User();
        $user = $userModel->findByEmail($email);
        
        if (!$user || !$userModel->verifyPassword($password, $user->password_hash)) {
            return false;
        }
        
        if (!$user->is_active) {
            return false;
        }
        
        // Log the user in
        $this->login($user, $remember);
        
        return true;
    }
    
    /**
     * Log a user in
     * 
     * @param object $user
     * @param bool $remember
     * @return void
     */
    public function login($user, $remember = false) {
        // Regenerate session ID to prevent session fixation
        Session::regenerate();
        
        // Store user ID in session
        Session::set('user_id', $user->id);
        
        // Handle remember me
        if ($remember) {
            $this->createRememberToken($user->id);
        } else {
            $this->clearRememberToken();
        }
        
        // Update last login
        $userModel = new User();
        $userModel->update($user->id, [
            'last_login' => date('Y-m-d H:i:s')
        ]);
        
        $this->user = $user;
    }
    
    /**
     * Log the user out
     * 
     * @return void
     */
    public function logout() {
        // Clear remember token
        $this->clearRememberToken();
        
        // Clear session
        Session::destroy();
        
        $this->user = null;
    }
    
    /**
     * Create remember token for "remember me" functionality
     * 
     * @param int $userId
     * @return void
     */
    protected function createRememberToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expires = time() + (86400 * 30); // 30 days
        
        // Store token in database
        $db = App::getInstance()->db();
        $stmt = $db->prepare(
            "INSERT INTO remember_tokens (user_id, token, expires_at) 
             VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE 
             token = VALUES(token), expires_at = VALUES(expires_at)"
        );
        
        $stmt->execute([$userId, $token, date('Y-m-d H:i:s', $expires)]);
        
        // Set cookie
        Cookie::set('remember_token', $token, $expires);
    }
    
    /**
     * Clear remember token
     * 
     * @return void
     */
    protected function clearRememberToken() {
        $token = Cookie::get('remember_token');
        
        if ($token) {
            // Delete token from database
            $db = App::getInstance()->db();
            $stmt = $db->prepare("DELETE FROM remember_tokens WHERE token = ?");
            $stmt->execute([$token]);
            
            // Delete cookie
            Cookie::delete('remember_token');
        }
    }
    
    /**
     * Attempt to log in using remember token
     * 
     * @param string $token
     * @return User|null
     */
    protected function attemptRememberLogin($token) {
        $db = App::getInstance()->db();
        
        // Find valid token
        $stmt = $db->prepare(
            "SELECT u.* FROM users u 
             JOIN remember_tokens rt ON u.id = rt.user_id 
             WHERE rt.token = ? AND rt.expires_at > NOW()"
        );
        
        $stmt->execute([$token]);
        $user = $stmt->fetch(\PDO::FETCH_OBJ);
        
        if ($user) {
            // Log the user in
            $this->login($user, true);
            return $user;
        }
        
        // Invalid token, delete the cookie
        $this->clearRememberToken();
        
        return null;
    }
    
    /**
     * Check if user has a specific role
     * 
     * @param string $role
     * @return bool
     */
    public function hasRole($role) {
        $user = $this->user();
        return $user && $user->role === $role;
    }
    
    /**
     * Check if user has any of the given roles
     * 
     * @param array $roles
     * @return bool
     */
    public function hasAnyRole(array $roles) {
        $user = $this->user();
        return $user && in_array($user->role, $roles);
    }
    

    /**
     * Check if the authenticated user is a counselor
     * 
     * @return bool
     */
    public function isCounselor() {
        return $this->hasRole('counselor');
    }
}
