<?php
// Test script for the chat API endpoint
echo "Testing API Endpoint...\n\n";

// Test data
$data = [
    'message' => 'Hello, how are you?',
    'context' => []
];

// Initialize cURL
$ch = curl_init('http://localhost/consultation_site/api/chat.php');

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

// Execute the request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Check for errors
if (curl_errno($ch)) {
    die('cURL Error: ' . curl_error($ch));
}

// Close cURL
curl_close($ch);

// Output the response
echo "HTTP Status: " . $httpCode . "\n";
echo "Response: \n";
print_r(json_decode($response, true));
?>
