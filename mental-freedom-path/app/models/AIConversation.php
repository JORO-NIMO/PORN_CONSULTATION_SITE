<?php
namespace App\Models;

use PDO;
use App\Core\App;

class AIConversation {
    protected $db;
    
    public function __construct() {
        $this->db = App::getInstance()->db();
    }
    
    /**
     * Create a new conversation
     */
    public function create($data) {
        $sql = "INSERT INTO ai_conversations (
                    user_id, 
                    title, 
                    is_active,
                    created_at,
                    updated_at
                ) VALUES (?, ?, ?, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['user_id'],
            $data['title'],
            $data['is_active'] ?? 0
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Update conversation
     */
    public function update($id, $data) {
        $updates = [];
        $params = [];
        
        $allowedFields = ['title', 'is_active', 'summary', 'sentiment'];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $updates[] = "$key = ?";
                $params[] = $value;
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $params[] = $id;
        
        $sql = "UPDATE ai_conversations SET " . implode(', ', $updates) . 
               ", updated_at = NOW() WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Delete conversation
     */
    public function delete($id) {
        // First delete all messages
        $this->db->prepare("DELETE FROM ai_messages WHERE conversation_id = ?")
                ->execute([$id]);
                
        // Then delete the conversation
        $stmt = $this->db->prepare("DELETE FROM ai_conversations WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Find conversation by ID
     */
    public function find($id) {
        $stmt = $this->db->prepare(
            "SELECT * FROM ai_conversations WHERE id = ?"
        );
        
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Get active conversation for user
     */
    public function getActiveConversation($userId) {
        $stmt = $this->db->prepare(
            "SELECT * FROM ai_conversations 
             WHERE user_id = ? AND is_active = 1 
             ORDER BY updated_at DESC 
             LIMIT 1"
        );
        
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Get user's conversations
     */
    public function getUserConversations($userId, $limit = 20) {
        $stmt = $this->db->prepare(
            "SELECT * FROM ai_conversations 
             WHERE user_id = ? 
             ORDER BY updated_at DESC 
             LIMIT ?"
        );
        
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Check if conversation belongs to user
     */
    public function belongsToUser($conversationId, $userId) {
        $stmt = $this->db->prepare(
            "SELECT id FROM ai_conversations 
             WHERE id = ? AND user_id = ?"
        );
        
        $stmt->execute([$conversationId, $userId]);
        return (bool)$stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Deactivate all user's conversations
     */
    public function deactivateUserConversations($userId) {
        $stmt = $this->db->prepare(
            "UPDATE ai_conversations 
             SET is_active = 0 
             WHERE user_id = ? AND is_active = 1"
        );
        
        return $stmt->execute([$userId]);
    }
    
    /**
     * Add message to conversation
     */
    public function addMessage($data) {
        $sql = "INSERT INTO ai_messages (
                    conversation_id, 
                    sender, 
                    message,
                    sentiment,
                    created_at
                ) VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['conversation_id'],
            $data['sender'],
            $data['message'],
            $data['sentiment'] ?? null,
            $data['created_at'] ?? date('Y-m-d H:i:s')
        ]);
        
        if ($result) {
            // Update conversation's updated_at timestamp
            $this->update($data['conversation_id'], [
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Get conversation messages
     */
    public function getMessages($conversationId, $limit = 50) {
        $stmt = $this->db->prepare(
            "SELECT * FROM ai_messages 
             WHERE conversation_id = ? 
             ORDER BY created_at ASC" . 
             ($limit ? " LIMIT $limit" : "")
        );
        
        $stmt->execute([$conversationId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get conversation context for AI
     */
    public function getConversationContext($conversationId, $messageLimit = 5) {
        // Get most recent messages
        $messages = $this->getMessages($conversationId, $messageLimit);
        
        // Get conversation summary if available
        $conversation = $this->find($conversationId);
        $summary = $conversation->summary ?? null;
        
        // Get any relevant resources
        $resourceModel = new AIResource();
        $resources = $resourceModel->findByConversation($conversationId);
        
        return [
            'summary' => $summary,
            'recent_messages' => $messages,
            'resources' => $resources
        ];
    }
    
    /**
     * Get conversation statistics
     */
    public function getStats($userId) {
        $stats = [
            'total_conversations' => 0,
            'total_messages' => 0,
            'active_conversations' => 0,
            'avg_messages_per_convo' => 0,
            'sentiment_distribution' => [
                'positive' => 0,
                'neutral' => 0,
                'negative' => 0
            ]
        ];
        
        // Get basic conversation stats
        $stmt = $this->db->prepare(
            "SELECT 
                COUNT(*) as total,
                SUM(is_active) as active
             FROM ai_conversations 
             WHERE user_id = ?"
        );
        
        $stmt->execute([$userId]);
        $convoStats = $stmt->fetch(PDO::FETCH_OBJ);
        
        if ($convoStats) {
            $stats['total_conversations'] = (int)$convoStats->total;
            $stats['active_conversations'] = (int)$convoStats->active;
        }
        
        // Get message stats
        $stmt = $this->db->prepare(
            "SELECT 
                COUNT(*) as total,
                AVG(message_count) as avg_messages
             FROM (
                 SELECT 
                     conversation_id, 
                     COUNT(*) as message_count
                 FROM ai_messages m
                 JOIN ai_conversations c ON m.conversation_id = c.id
                 WHERE c.user_id = ?
                 GROUP BY conversation_id
             ) as convos"
        );
        
        $stmt->execute([$userId]);
        $msgStats = $stmt->fetch(PDO::FETCH_OBJ);
        
        if ($msgStats) {
            $stats['total_messages'] = (int)$msgStats->total;
            $stats['avg_messages_per_convo'] = round($msgStats->avg_messages, 1);
        }
        
        // Get sentiment distribution
        $stmt = $this->db->prepare(
            "SELECT 
                sentiment, 
                COUNT(*) as count
             FROM ai_messages m
             JOIN ai_conversations c ON m.conversation_id = c.id
             WHERE c.user_id = ? AND sentiment IS NOT NULL
             GROUP BY sentiment"
        );
        
        $stmt->execute([$userId]);
        $sentiments = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        foreach ($sentiments as $sentiment) {
            if (isset($stats['sentiment_distribution'][$sentiment->sentiment])) {
                $stats['sentiment_distribution'][$sentiment->sentiment] = (int)$sentiment->count;
            }
        }
        
        return $stats;
    }
}
