<?php

namespace Tests\Feature;

use Tests\TestCase\TestCase;
use App\Models\User;
use App\Models\AIConversation;
use App\Models\AIMessage;

class AIConversationFlowTest extends TestCase
{
    private $user;
    private $conversation;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = new User([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT)
        ]);
        
        // Save user to database (assuming your User model has a save method)
        $this->user->save();
        
        // Create a test conversation
        $this->conversation = new AIConversation([
            'user_id' => $this->user->id,
            'title' => 'Test Conversation',
            'context' => 'general',
            'model' => 'gpt-4',
            'is_archived' => false
        ]);
        $this->conversation->save();
    }
    
    public function testStartNewConversation()
    {
        // Simulate starting a new conversation
        $newConversation = new AIConversation([
            'user_id' => $this->user->id,
            'title' => 'New Test Conversation',
            'context' => 'anxiety',
            'model' => 'gpt-4',
            'is_archived' => false
        ]);
        
        $saved = $newConversation->save();
        
        $this->assertTrue($saved);
        $this->assertNotNull($newConversation->id);
        $this->assertEquals('New Test Conversation', $newConversation->title);
        $this->assertEquals('anxiety', $newConversation->context);
    }
    
    public function testAddMessageToConversation()
    {
        // Add a user message
        $userMessage = new AIMessage([
            'conversation_id' => $this->conversation->id,
            'role' => 'user',
            'content' => 'I\'ve been feeling anxious lately.',
            'sentiment' => 'negative',
            'sentiment_score' => -0.7
        ]);
        $userMessage->save();
        
        // Add an AI response
        $aiMessage = new AIMessage([
            'conversation_id' => $this->conversation->id,
            'role' => 'assistant',
            'content' => 'I\'m sorry to hear that. Can you tell me more about what\'s been on your mind?',
            'sentiment' => 'neutral',
            'sentiment_score' => 0.1
        ]);
        $aiMessage->save();
        
        // Retrieve messages for the conversation
        $messages = AIMessage::where('conversation_id', $this->conversation->id)
            ->orderBy('created_at', 'asc')
            ->get();
        
        $this->assertCount(2, $messages);
        $this->assertEquals('user', $messages[0]->role);
        $this->assertEquals('I\'ve been feeling anxious lately.', $messages[0]->content);
        $this->assertEquals('assistant', $messages[1]->role);
        $this->assertStringContainsString('sorry to hear that', $messages[1]->content);
    }
    
    public function testConversationHistory()
    {
        // Add multiple messages to test conversation history
        $messages = [
            ['role' => 'user', 'content' => 'Hello', 'sentiment' => 'neutral'],
            ['role' => 'assistant', 'content' => 'Hi there! How can I help you today?', 'sentiment' => 'positive'],
            ['role' => 'user', 'content' => 'I need some advice', 'sentiment' => 'neutral'],
            ['role' => 'assistant', 'content' => 'I\'m here to help. What would you like to talk about?', 'sentiment' => 'positive']
        ];
        
        foreach ($messages as $msg) {
            $message = new AIMessage([
                'conversation_id' => $this->conversation->id,
                'role' => $msg['role'],
                'content' => $msg['content'],
                'sentiment' => $msg['sentiment'],
                'sentiment_score' => 0.5
            ]);
            $message->save();
        }
        
        // Get conversation history
        $history = AIMessage::where('conversation_id', $this->conversation->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->toArray();
        
        $this->assertCount(4, $history);
        $this->assertEquals('Hello', $history[0]['content']);
        $this->assertEquals('Hi there!', substr($history[1]['content'], 0, 10));
    }
    
    public function testConversationDeletion()
    {
        // Test soft delete
        $conversationId = $this->conversation->id;
        $deleted = $this->conversation->delete();
        
        $this->assertTrue($deleted);
        
        // Should still exist in database with deleted_at timestamp
        $deletedConversation = AIConversation::withTrashed()->find($conversationId);
        $this->assertNotNull($deletedConversation->deleted_at);
        
        // Should not appear in regular queries
        $this->assertNull(AIConversation::find($conversationId));
    }
    
    protected function tearDown(): void
    {
        // Clean up test data
        if ($this->conversation) {
            AIMessage::where('conversation_id', $this->conversation->id)->delete();
            $this->conversation->forceDelete();
        }
        
        if ($this->user) {
            $this->user->delete();
        }
        
        parent::tearDown();
    }
}
