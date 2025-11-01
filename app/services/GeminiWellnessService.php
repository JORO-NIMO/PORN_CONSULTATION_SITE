<?php
class GeminiWellnessService {
    private $apiKey;
    private $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';
    
    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }
    
    public function getWellnessResponse($message, $context = '') {
        $prompt = "You are a compassionate mental wellness assistant. " . 
                 "Provide supportive, empathetic, and helpful responses. " .
                 "If the user seems to be in crisis, provide appropriate resources.\n\n" .
                 "Context: $context\n\nUser: $message";
        
        $data = [
            'contents' => [
                'parts' => [
                    ['text' => $prompt]
                ]
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_NONE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_NONE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_NONE'
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_NONE'
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 1000,
                'topP' => 0.8,
                'topK' => 40
            ]
        ];
        
        $url = $this->apiUrl . '?key=' . $this->apiKey;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $responseText = $result['candidates'][0]['content']['parts'][0]['text'];
                return [
                    'response' => $responseText,
                    'is_crisis' => $this->checkForCrisis($responseText)
                ];
            }
        }
        
        // Log error for debugging
        error_log("Gemini API Error: " . $response);
        
        return [
            'response' => "I'm sorry, I'm having trouble connecting to the wellness assistant right now. " .
                        "Please try again later or contact support if the issue persists.",
            'is_crisis' => false
        ];
    }
    
    private function checkForCrisis($response) {
        $crisisKeywords = [
            'suicide', 'self-harm', 'kill myself', 'end my life',
            'emergency', 'crisis', 'helpline', 'hotline',
            '911', '112', '999', 'suicidal', 'self harm'
        ];
        
        foreach ($crisisKeywords as $keyword) {
            if (stripos($response, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }
}
