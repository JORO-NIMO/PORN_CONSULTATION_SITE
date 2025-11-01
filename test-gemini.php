<?php
// Test script for Gemini API integration
require_once 'config.php';
require_once 'app/services/GeminiWellnessService.php';

// Check if API key is set
if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY') {
    die("Error: Please set your Gemini API key in config.php\n");
}

// Create an instance of the service
$service = new GeminiWellnessService(GEMINI_API_KEY);

// Test message
$testMessage = "Hello, I've been feeling really stressed lately. Can you help me with some relaxation techniques?";

echo "Sending test message to Gemini API...\n";
echo "Message: $testMessage\n\n";

try {
    // Get response from Gemini
    $response = $service->getWellnessResponse($testMessage);
    
    // Display the response
    echo "Response received successfully!\n";
    echo "Response: " . $response['response'] . "\n";
    echo "Is Crisis: " . ($response['is_crisis'] ? 'Yes' : 'No') . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if (isset($httpCode)) {
        echo "HTTP Code: $httpCode\n";
    }
    if (isset($response)) {
        echo "API Response: " . print_r($response, true) . "\n";
    }
}
?>
