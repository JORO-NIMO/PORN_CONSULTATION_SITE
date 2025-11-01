<?php
namespace App\Controllers;

use App\Core\Controller;
// Use the canonical Gemini wellness service from the root application
require_once __DIR__ . '/../../../app/services/GeminiWellnessService.php';
use App\Models\AIConversation;
use App\Models\AIResource;

class AIAssistantController extends Controller {
    protected $aiService;
    protected $conversationModel;
    protected $resourceModel;
    
    public function __construct() {
        parent::__construct();
        // Initialize Gemini wellness service
        $apiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : getenv('GEMINI_API_KEY');
        $this->aiService = new \GeminiWellnessService($apiKey);
        $this->conversationModel = new AIConversation();
        $this->resourceModel = new AIResource();
        $this->requireAuth();
    }
    
    /**
     * Show AI Assistant chat interface
     */
    public function chat() {
        $userId = $this->auth->id();
        
        // Get or create active conversation
        $conversation = $this->conversationModel->getActiveConversation($userId);
        
        if (!$conversation) {
            $conversationId = $this->conversationModel->create([
                'user_id' => $userId,
                'title' => 'New Chat ' . date('M j, Y'),
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $conversation = $this->conversationModel->find($conversationId);
        }
        
        // Get conversation history
        $messages = $this->conversationModel->getMessages($conversation->id);
        
        // Get suggested resources based on conversation
        $suggestedResources = $this->getSuggestedResources($messages);
        
        $this->view('ai-assistant/chat', [
            'title' => 'AI Wellness Assistant',
            'conversation' => $conversation,
            'messages' => $messages,
            'suggestedResources' => $suggestedResources,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    /**
     * Handle AI chat message
     */
    public function sendMessage() {
        $this->validateCsrfToken();
        
        $userId = $this->auth->id();
        $conversationId = $this->request->post('conversation_id');
        $message = trim($this->request->post('message'));
        
        if (empty($message)) {
            return $this->json(['error' => 'Message cannot be empty'], 400);
        }
        
        // Verify conversation belongs to user
        if (!$this->conversationModel->belongsToUser($conversationId, $userId)) {
            return $this->json(['error' => 'Invalid conversation'], 403);
        }
        
        try {
            // Add user message to conversation
            $this->conversationModel->addMessage([
                'conversation_id' => $conversationId,
                'sender' => 'user',
                'message' => $message,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Get conversation context
            $context = $this->conversationModel->getConversationContext($conversationId);
            
            // Get AI response via Gemini
            $gemini = $this->aiService->getWellnessResponse($message, $context);
            $response = [
                'text' => $gemini['response'] ?? '',
                'sentiment' => null,
                'is_crisis' => $gemini['is_crisis'] ?? false
            ];
            
            // Add AI response to conversation
            $this->conversationModel->addMessage([
                'conversation_id' => $conversationId,
                'sender' => 'assistant',
                'message' => $response['text'],
                'sentiment' => $response['sentiment'] ?? 'neutral',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Update conversation title if it's the first message
            $this->updateConversationTitle($conversationId, $message);
            
            // Get suggested resources based on the response
            $suggestedResources = $this->getSuggestedResources(
                $this->conversationModel->getMessages($conversationId, 5)
            );
            
            return $this->json([
                'success' => true,
                'response' => $response,
                'suggestedResources' => $suggestedResources
            ]);
            
        } catch (\Exception $e) {
            error_log('AI Assistant Error: ' . $e->getMessage());
            return $this->json([
                'error' => 'Sorry, I encountered an error. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Get conversation history
     */
    public function getConversation($id) {
        $userId = $this->auth->id();
        
        // Verify conversation belongs to user
        if (!$this->conversationModel->belongsToUser($id, $userId)) {
            return $this->json(['error' => 'Conversation not found'], 404);
        }
        
        $messages = $this->conversationModel->getMessages($id);
        
        return $this->json([
            'success' => true,
            'messages' => $messages
        ]);
    }
    
    /**
     * Get list of user's conversations
     */
    public function getConversations() {
        $userId = $this->auth->id();
        $conversations = $this->conversationModel->getUserConversations($userId);
        
        return $this->json([
            'success' => true,
            'conversations' => $conversations
        ]);
    }
    
    /**
     * Create a new conversation
     */
    public function createConversation() {
        $userId = $this->auth->id();
        
        // Deactivate other conversations
        $this->conversationModel->deactivateUserConversations($userId);
        
        // Create new conversation
        $conversationId = $this->conversationModel->create([
            'user_id' => $userId,
            'title' => 'New Chat ' . date('M j, Y'),
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return $this->json([
            'success' => true,
            'conversation_id' => $conversationId,
            'redirect' => '/ai-assistant/chat?conversation=' . $conversationId
        ]);
    }
    
    /**
     * Update conversation title
     */
    public function updateTitle($id) {
        $userId = $this->auth->id();
        $title = trim($this->request->post('title'));
        
        if (empty($title)) {
            return $this->json(['error' => 'Title cannot be empty'], 400);
        }
        
        // Verify conversation belongs to user
        if (!$this->conversationModel->belongsToUser($id, $userId)) {
            return $this->json(['error' => 'Conversation not found'], 404);
        }
        
        $this->conversationModel->update($id, [
            'title' => $title
        ]);
        
        return $this->json([
            'success' => true,
            'title' => $title
        ]);
    }
    
    /**
     * Delete conversation
     */
    public function deleteConversation($id) {
        $userId = $this->auth->id();
        
        // Verify conversation belongs to user
        if (!$this->conversationModel->belongsToUser($id, $userId)) {
            return $this->json(['error' => 'Conversation not found'], 404);
        }
        
        $this->conversationModel->delete($id);
        
        return $this->json([
            'success' => true,
            'redirect' => '/ai-assistant/chat'
        ]);
    }
    
    /**
     * Get suggested resources based on conversation
     */
    protected function getSuggestedResources($messages, $limit = 3) {
        if (empty($messages)) {
            return [];
        }
        
        // Extract keywords from messages
        $keywords = $this->extractKeywords($messages);
        
        if (empty($keywords)) {
            return [];
        }
        
        // Get resources matching keywords
        return $this->resourceModel->findByKeywords($keywords, $limit);
    }
    
    /**
     * Extract keywords from messages
     */
    protected function extractKeywords($messages) {
        // Combine all messages into a single text
        $text = '';
        foreach ($messages as $message) {
            $text .= ' ' . $message->message;
        }
        
        // Remove common words and extract keywords
        $stopWords = $this->getStopWords();
        $words = str_word_count(strtolower($text), 1);
        $words = array_diff($words, $stopWords);
        
        // Count word frequencies
        $wordCounts = array_count_values($words);
        arsort($wordCounts);
        
        // Return top 5 keywords
        return array_slice(array_keys($wordCounts), 0, 5, true);
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
     * Update conversation title based on first message
     */
    protected function updateConversationTitle($conversationId, $firstMessage) {
        $conversation = $this->conversationModel->find($conversationId);
        
        // Only update if it's the default title
        if (strpos($conversation->title, 'New Chat') === 0) {
            // Generate a title from the first message (first 30 chars)
            $title = trim(substr($firstMessage, 0, 30));
            if (strlen($firstMessage) > 30) {
                $title .= '...';
            }
            
            $this->conversationModel->update($conversationId, [
                'title' => $title
            ]);
        }
    }
}
