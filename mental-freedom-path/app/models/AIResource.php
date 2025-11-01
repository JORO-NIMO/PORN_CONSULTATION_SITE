<?php
namespace App\Models;

use PDO;
use App\Core\App;

class AIResource {
    protected $db;
    
    public function __construct() {
        $this->db = App::getInstance()->db();
    }
    
    /**
     * Find resources by keywords
     */
    public function findByKeywords($keywords, $limit = 5) {
        if (empty($keywords)) {
            return [];
        }
        
        $placeholders = rtrim(str_repeat('?,', count($keywords)), ',');
        $searchTerms = [];
        
        // Prepare search terms for each keyword
        foreach ($keywords as $keyword) {
            $searchTerms[] = "%{$keyword}%";
        }
        
        // Search in title, description, and tags
        $sql = "SELECT * FROM ai_resources 
                WHERE is_active = 1 AND (
                    title LIKE ? 
                    OR description LIKE ? 
                    OR tags LIKE ?
                    " . (count($keywords) > 1 ? 
                    "OR " . implode(" OR ", array_fill(0, count($keywords) - 1, "tags LIKE ?")) : "") . "
                )
                ORDER BY 
                    CASE 
                        WHEN title LIKE ? THEN 1
                        WHEN description LIKE ? THEN 2
                        ELSE 3
                    END,
                    relevance_score DESC
                LIMIT ?";
        
        // Prepare parameters
        $params = [];
        foreach ($searchTerms as $term) {
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }
        
        // Add the first term again for the ORDER BY clause
        if (!empty($searchTerms)) {
            $params[] = $searchTerms[0];
            $params[] = $searchTerms[0];
        }
        
        $params[] = $limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Find resources by category
     */
    public function findByCategory($category, $limit = 10) {
        $stmt = $this->db->prepare(
            "SELECT * FROM ai_resources 
             WHERE category = ? AND is_active = 1
             ORDER BY relevance_score DESC
             LIMIT ?"
        );
        
        $stmt->execute([$category, $limit]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Find resources related to a conversation
     */
    public function findByConversation($conversationId, $limit = 3) {
        // First, get keywords from the conversation
        $keywords = $this->extractKeywordsFromConversation($conversationId);
        
        if (empty($keywords)) {
            return [];
        }
        
        return $this->findByKeywords($keywords, $limit);
    }
    
    /**
     * Get recommended resources for a user
     */
    public function getRecommendedResources($userId, $limit = 5) {
        // Get user's recent conversations
        $conversationModel = new AIConversation();
        $conversations = $conversationModel->getUserConversations($userId, 3);
        
        $allKeywords = [];
        
        // Extract keywords from all conversations
        foreach ($conversations as $conversation) {
            $keywords = $this->extractKeywordsFromConversation($conversation->id);
            $allKeywords = array_merge($allKeywords, $keywords);
        }
        
        // Get unique keywords
        $allKeywords = array_unique($allKeywords);
        
        // If no keywords from conversations, return general resources
        if (empty($allKeywords)) {
            return $this->getGeneralResources($limit);
        }
        
        // Find resources based on conversation keywords
        return $this->findByKeywords($allKeywords, $limit);
    }
    
    /**
     * Get general mental health resources
     */
    public function getGeneralResources($limit = 5) {
        $stmt = $this->db->prepare(
            "SELECT * FROM ai_resources 
             WHERE is_featured = 1 AND is_active = 1
             ORDER BY relevance_score DESC
             LIMIT ?"
        );
        
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Extract keywords from a conversation
     */
    protected function extractKeywordsFromConversation($conversationId) {
        // Get messages from the conversation
        $stmt = $this->db->prepare(
            "SELECT message FROM ai_messages 
             WHERE conversation_id = ? 
             ORDER BY created_at DESC
             LIMIT 10"
        );
        
        $stmt->execute([$conversationId]);
        $messages = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($messages)) {
            return [];
        }
        
        // Combine messages into a single text
        $text = implode(" ", $messages);
        
        // Remove common words and extract keywords
        $stopWords = $this->getStopWords();
        $words = str_word_count(strtolower($text), 1);
        $words = array_diff($words, $stopWords);
        
        // Count word frequencies
        $wordCounts = array_count_values($words);
        arsort($wordCounts);
        
        // Filter out words that appear only once
        $keywords = [];
        foreach ($wordCounts as $word => $count) {
            if ($count > 1 && strlen($word) > 3) {
                $keywords[] = $word;
                
                // Limit to top 10 keywords
                if (count($keywords) >= 10) {
                    break;
                }
            }
        }
        
        return $keywords;
    }
    
    /**
     * Get list of common stop words
     */
    protected function getStopWords() {
        return [
            'a', 'an', 'and', 'are', 'as', 'at', 'be', 'but', 'by', 'for', 'if', 'in', 'into', 'is', 'it',
            'no', 'not', 'of', 'on', 'or', 'such', 'that', 'the', 'their', 'then', 'there', 'these',
            'they', 'this', 'to', 'was', 'will', 'with', 'i', 'you', 'he', 'she', 'we', 'they', 'me', 'him',
            'her', 'us', 'them', 'my', 'your', 'his', 'its', 'our', 'their', 'mine', 'yours', 'hers', 'ours',
            'theirs', 'this', 'that', 'these', 'those', 'am', 'is', 'are', 'was', 'were', 'be', 'been',
            'being', 'have', 'has', 'had', 'having', 'do', 'does', 'did', 'doing', 'a', 'an', 'the', 'and',
            'but', 'if', 'or', 'because', 'as', 'until', 'while', 'of', 'at', 'by', 'for', 'with', 'about',
            'against', 'between', 'into', 'through', 'during', 'before', 'after', 'above', 'below', 'to',
            'from', 'up', 'down', 'in', 'out', 'on', 'off', 'over', 'under', 'again', 'further', 'then',
            'once', 'here', 'there', 'when', 'where', 'why', 'how', 'all', 'any', 'both', 'each', 'few',
            'more', 'most', 'other', 'some', 'such', 'no', 'nor', 'not', 'only', 'own', 'same', 'so', 'than',
            'too', 'very', 'can', 'will', 'just', 'don', 'should', 'now', 'd', 'll', 'm', 'o', 're', 've',
            'y', 'ain', 'aren', 'couldn', 'didn', 'doesn', 'hadn', 'hasn', 'haven', 'isn', 'ma', 'mightn',
            'mustn', 'needn', 'shan', 'shouldn', 'wasn', 'weren', 'won', 'wouldn'
        ];
    }
    
    /**
     * Get resource by ID
     */
    public function find($id) {
        $stmt = $this->db->prepare(
            "SELECT * FROM ai_resources WHERE id = ?"
        );
        
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Get all resources with pagination
     */
    public function getAll($page = 1, $perPage = 10, $filters = []) {
        $where = ["is_active = 1"];
        $params = [];
        
        // Apply filters
        if (!empty($filters['category'])) {
            $where[] = "category = ?";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(title LIKE ? OR description LIKE ? OR tags LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";
        
        // Get total count for pagination
        $countStmt = $this->db->prepare(
            "SELECT COUNT(*) as total FROM ai_resources $whereClause"
        );
        $countStmt->execute($params);
        $total = $countStmt->fetch(PDO::FETCH_OBJ)->total;
        
        // Calculate pagination
        $offset = ($page - 1) * $perPage;
        $totalPages = ceil($total / $perPage);
        
        // Get resources for current page
        $stmt = $this->db->prepare(
            "SELECT * FROM ai_resources 
             $whereClause
             ORDER BY is_featured DESC, relevance_score DESC
             LIMIT ? OFFSET ?"
        );
        
        // Add limit and offset to params
        $stmt->execute(array_merge($params, [$perPage, $offset]));
        $resources = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        return [
            'data' => $resources,
            'pagination' => [
                'total' => (int)$total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => $totalPages,
                'has_more' => $page < $totalPages
            ]
        ];
    }
    
    /**
     * Create a new resource
     */
    public function create($data) {
        $sql = "INSERT INTO ai_resources (
                    title, 
                    description, 
                    url,
                    category,
                    resource_type,
                    tags,
                    is_featured,
                    is_active,
                    relevance_score,
                    created_at,
                    updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['title'],
            $data['description'] ?? null,
            $data['url'],
            $data['category'] ?? 'general',
            $data['resource_type'] ?? 'article',
            is_array($data['tags'] ?? null) ? implode(',', $data['tags']) : ($data['tags'] ?? ''),
            $data['is_featured'] ?? 0,
            $data['is_active'] ?? 1,
            $data['relevance_score'] ?? 0
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Update a resource
     */
    public function update($id, $data) {
        $updates = [];
        $params = [];
        
        $allowedFields = [
            'title', 'description', 'url', 'category', 'resource_type',
            'tags', 'is_featured', 'is_active', 'relevance_score'
        ];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                // Handle array values (like tags)
                if ($key === 'tags' && is_array($value)) {
                    $value = implode(',', $value);
                }
                
                $updates[] = "$key = ?";
                $params[] = $value;
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $params[] = $id;
        
        $sql = "UPDATE ai_resources SET " . implode(', ', $updates) . 
               ", updated_at = NOW() WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Delete a resource
     */
    public function delete($id) {
        $stmt = $this->db->prepare(
            "DELETE FROM ai_resources WHERE id = ?"
        );
        
        return $stmt->execute([$id]);
    }
    
    /**
     * Get all available categories
     */
    public function getCategories() {
        $stmt = $this->db->query(
            "SELECT DISTINCT category 
             FROM ai_resources 
             WHERE is_active = 1 
             ORDER BY category"
        );
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Get all available resource types
     */
    public function getResourceTypes() {
        $stmt = $this->db->query(
            "SELECT DISTINCT resource_type 
             FROM ai_resources 
             WHERE is_active = 1 
             ORDER BY resource_type"
        );
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
