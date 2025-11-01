<?php
require_once 'config/config.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit();
}

// Set page title
$pageTitle = 'Chat with Granny - ' . SITE_NAME;

// Include header
include 'includes/header.php';
?>

<main class="main-content">
    <div class="container">
        <h1 class="page-title">Chat with Granny</h1>
        <p class="lead">Your compassionate AI wellness assistant is here to listen and help 24/7.</p>
        
        <div class="chat-container">
            <div class="chat-header">
                <i class="fas fa-comments me-2"></i>
                Granny - Your Wellness Assistant
            </div>
            <div id="wellness-chat-messages" class="chat-messages">
                <!-- Messages will appear here -->
            </div>
            <div class="chat-input-container">
                <div id="typing-indicator" class="typing-indicator">
                    <span></span><span></span><span></span>
                </div>
                <textarea id="chat-input" class="chat-input" placeholder="Type your message to Granny..." rows="1"></textarea>
                <button id="send-message" class="send-button">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</main>

<style>
/* Chat Interface Styles */
.chat-container {
    max-width: 800px;
    margin: 2rem auto;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    background: white;
    display: flex;
    flex-direction: column;
    height: 70vh;
}

.chat-header {
    background: var(--primary-color);
    color: white;
    padding: 1rem;
    font-size: 1.2rem;
    font-weight: 500;
    display: flex;
    align-items: center;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.message {
    max-width: 80%;
    padding: 0.8rem 1.2rem;
    border-radius: 15px;
    line-height: 1.5;
    position: relative;
    word-wrap: break-word;
}

.message.user {
    align-self: flex-end;
    background: var(--primary-color);
    color: white;
    border-bottom-right-radius: 0;
}

.message.assistant {
    align-self: flex-start;
    background: #f0f2f5;
    color: #050505;
    border-bottom-left-radius: 0;
}

.chat-input-container {
    position: relative;
    border-top: 1px solid #ddd;
    padding: 1rem;
    background: white;
}

.chat-input {
    width: 100%;
    border: 1px solid #ddd;
    border-radius: 20px;
    padding: 0.8rem 3.5rem 0.8rem 1.2rem;
    font-size: 1rem;
    resize: none;
    min-height: 50px;
    max-height: 150px;
    transition: border-color 0.3s;
}

.chat-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(74, 111, 165, 0.2);
}

.send-button {
    position: absolute;
    right: 1.5rem;
    bottom: 1.5rem;
    background: var(--primary-color);
    color: white;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.3s;
}

.send-button:hover {
    background: #3a5a8a;
}

.typing-indicator {
    display: none;
    padding: 0.5rem 1rem;
    font-style: italic;
    color: #666;
    text-align: left;
}

.typing-indicator span {
    display: inline-block;
    width: 8px;
    height: 8px;
    background-color: #666;
    border-radius: 50%;
    margin: 0 2px;
    opacity: 0.4;
}

.typing-indicator.active {
    display: block;
}

.typing-indicator span:nth-child(1) {
    animation: bounce 1s infinite;
}

.typing-indicator span:nth-child(2) {
    animation: bounce 1s infinite 0.2s;
}

.typing-indicator span:nth-child(3) {
    animation: bounce 1s infinite 0.4s;
}

@keyframes bounce {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-5px); }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .chat-container {
        margin: 0;
        border-radius: 0;
        height: calc(100vh - 60px);
    }
    
    .message {
        max-width: 90%;
    }
}
</style>

<script src="assets/js/wellness-chat.js"></script>

<?php include 'includes/footer.php'; ?>
