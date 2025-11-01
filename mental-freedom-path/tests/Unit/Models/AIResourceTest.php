<?php

namespace Tests\Unit\Models;

use Tests\TestCase\TestCase;
use App\Models\AIResource;
use App\Models\User;

class AIResourceTest extends TestCase
{
    private $user;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = new User([
            'username' => 'resourcetest',
            'email' => 'resource@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT)
        ]);
        $this->user->save();
    }
    
    public function testCreateResource()
    {
        $resource = new AIResource([
            'title' => 'Mindfulness Meditation Guide',
            'description' => 'A comprehensive guide to mindfulness meditation',
            'type' => 'article',
            'url' => 'https://example.com/mindfulness',
            'content' => 'Mindfulness meditation is a practice...',
            'categories' => json_encode(['mindfulness', 'meditation', 'stress-relief']),
            'language' => 'en',
            'is_public' => true,
            'created_by' => $this->user->id
        ]);
        
        $saved = $resource->save();
        
        $this->assertTrue($saved);
        $this->assertNotNull($resource->id);
        $this->assertEquals('Mindfulness Meditation Guide', $resource->title);
        $this->assertEquals('article', $resource->type);
        $this->assertTrue($resource->is_public);
    }
    
    public function testSearchResources()
    {
        // Create test resources
        $resources = [
            [
                'title' => 'Dealing with Anxiety',
                'type' => 'article',
                'categories' => ['anxiety', 'mental-health'],
                'content' => 'Strategies to manage anxiety in daily life.'
            ],
            [
                'title' => 'Breathing Exercises',
                'type' => 'video',
                'categories' => ['stress-relief', 'mindfulness'],
                'content' => 'Guided breathing exercises to reduce stress.'
            ],
            [
                'title' => 'Sleep Hygiene Tips',
                'type' => 'article',
                'categories' => ['sleep', 'wellness'],
                'content' => 'Improve your sleep with these simple tips.'
            ],
        ];
        
        foreach ($resources as $data) {
            $resource = new AIResource([
                'title' => $data['title'],
                'description' => $data['content'],
                'type' => $data['type'],
                'url' => 'https://example.com/' . strtolower(str_replace(' ', '-', $data['title'])),
                'content' => $data['content'],
                'categories' => json_encode($data['categories']),
                'language' => 'en',
                'is_public' => true,
                'created_by' => $this->user->id
            ]);
            $resource->save();
        }
        
        // Test search by keyword
        $anxietyResources = AIResource::search('anxiety');
        $this->assertCount(1, $anxietyResources);
        $this->assertEquals('Dealing with Anxiety', $anxietyResources[0]->title);
        
        // Test search by category
        $mindfulnessResources = AIResource::search(null, ['mindfulness']);
        $this->assertCount(1, $mindfulnessResources);
        $this->assertEquals('Breathing Exercises', $mindfulnessResources[0]->title);
        
        // Test search by type
        $videoResources = AIResource::search(null, [], 'video');
        $this->assertCount(1, $videoResources);
        $this->assertEquals('video', $videoResources[0]->type);
    }
    
    public function testGetResourcesByType()
    {
        // Create test resources with different types
        $types = ['article', 'video', 'worksheet', 'podcast'];
        
        foreach ($types as $type) {
            $resource = new AIResource([
                'title' => ucfirst($type) . ' Resource',
                'description' => 'This is a ' . $type . ' resource',
                'type' => $type,
                'url' => 'https://example.com/' . $type,
                'content' => 'Content for ' . $type,
                'categories' => json_encode(['test']),
                'language' => 'en',
                'is_public' => true,
                'created_by' => $this->user->id
            ]);
            $resource->save();
        }
        
        // Test getting resources by type
        $videos = AIResource::getByType('video');
        $this->assertCount(1, $videos);
        $this->assertEquals('video', $videos[0]->type);
        
        $articles = AIResource::getByType('article');
        $this->assertCount(1, $articles);
        $this->assertEquals('article', $articles[0]->type);
    }
    
    public function testGetResourcesByLanguage()
    {
        // Create test resources with different languages
        $languages = ['en', 'es', 'fr'];
        
        foreach ($languages as $lang) {
            $resource = new AIResource([
                'title' => 'Resource in ' . strtoupper($lang),
                'description' => 'This is a resource in ' . $lang,
                'type' => 'article',
                'url' => 'https://example.com/resource-' . $lang,
                'content' => 'Content in ' . $lang,
                'categories' => json_encode(['test']),
                'language' => $lang,
                'is_public' => true,
                'created_by' => $this->user->id
            ]);
            $resource->save();
        }
        
        // Test getting resources by language
        $spanishResources = AIResource::getByLanguage('es');
        $this->assertCount(1, $spanishResources);
        $this->assertEquals('es', $spanishResources[0]->language);
        
        $englishResources = AIResource::getByLanguage('en');
        $this->assertCount(1, $englishResources);
        $this->assertEquals('en', $englishResources[0]->language);
    }
    
    protected function tearDown(): void
    {
        // Clean up test data
        AIResource::where('created_by', $this->user->id)->delete();
        
        if ($this->user) {
            $this->user->delete();
        }
        
        parent::tearDown();
    }
}
