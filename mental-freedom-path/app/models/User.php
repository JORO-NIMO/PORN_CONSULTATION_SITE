<?php
namespace App\Models;

use PDO;
use App\Core\App;

class User {
    protected $db;
    
    public function __construct() {
        $this->db = App::getInstance()->db();
    }
    
    /**
     * Find user by ID
     */
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Find user by email
     */
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? AND deleted_at IS NULL");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Create new user
     */
    public function create($data) {
        $sql = "INSERT INTO users (username, email, password_hash, first_name, last_name, date_of_birth, gender, role) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['username'],
            $data['email'],
            $data['password_hash'],
            $data['first_name'],
            $data['last_name'],
            $data['date_of_birth'] ?? null,
            $data['gender'] ?? null,
            $data['role'] ?? 'user'
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Update user
     */
    public function update($id, $data) {
        $updates = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $updates[] = "$key = ?";
            $params[] = $value;
        }
        
        $params[] = $id;
        
        $sql = "UPDATE users SET " . implode(', ', $updates) . 
               ", updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Delete user (soft delete)
     */
    public function delete($id) {
        $sql = "UPDATE users SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Verify user password
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Hash password
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
    }
    
    /**
     * Create password reset token
     */
    public function createPasswordResetToken($email) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $this->db->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);
        
        $stmt = $this->db->prepare(
            "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)"
        );
        
        return $stmt->execute([$email, $token, $expires]) ? $token : false;
    }
    
    /**
     * Validate password reset token
     */
    public function validatePasswordResetToken($token) {
        $stmt = $this->db->prepare(
            "SELECT * FROM password_resets 
             WHERE token = ? AND used = 0 AND expires_at > NOW()"
        );
        
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Mark password reset token as used
     */
    public function markTokenAsUsed($tokenId) {
        $stmt = $this->db->prepare(
            "UPDATE password_resets SET used = 1 WHERE id = ?"
        );
        
        return $stmt->execute([$tokenId]);
    }
}
