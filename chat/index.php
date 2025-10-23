<?php
/**
 * Chat Interface
 */

require_once __DIR__ . '/../config/config.php';
requireLogin();

// Initialize database connection
$db = Database::getInstance();

$pageTitle = 'Support Chat';
$currentUser = getCurrentUser();

// Get chat rooms
$rooms = $db->fetchAll("SELECT * FROM chat_rooms WHERE is_public = 1 ORDER BY name");
$currentRoomId = $_GET['room'] ?? 1;

// Get room info
$currentRoom = $db->fetchOne("SELECT * FROM chat_rooms WHERE id = ?", [$currentRoomId]);
if (!$currentRoom) {
    $currentRoom = ['id' => 1, 'name' => 'General Support', 'description' => 'General support chat'];
    $currentRoomId = 1;
}

// Include header
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            <!-- Chat Rooms -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Chat Rooms</h5>
                    <?php if (isAdmin()): ?>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createRoomModal">
                            <i class="fas fa-plus"></i>
                        </button>
                    <?php endif; ?>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($rooms as $room): ?>
                        <a href="?room=<?php echo $room['id']; ?>" 
                           class="list-group-item list-group-item-action <?php echo $room['id'] == $currentRoomId ? 'active' : ''; ?>">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><?php echo htmlspecialchars($room['name']); ?></h6>
                                <span class="badge bg-<?php echo $room['id'] == $currentRoomId ? 'light' : 'primary'; ?> rounded-pill">
                                    <?php 
                                    $count = $db->fetchOne("SELECT COUNT(*) as count FROM chat_messages WHERE room_id = ?", [$room['id']]);
                                    echo $count['count'];
                                    ?>
                                </span>
                            </div>
                            <small><?php echo htmlspecialchars($room['description']); ?></small>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Online Users -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Online Users <span id="online-count" class="badge bg-primary rounded-pill">0</span></h5>
                </div>
                <div class="card-body p-0">
                    <div id="user-list" class="list-group list-group-flush">
                        <!-- Users will be populated by JavaScript -->
                        <div class="text-center py-3 text-muted">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            Loading users...
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><?php echo htmlspecialchars($currentRoom['name']); ?></h4>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="chat-settings-btn">
                            <i class="fas fa-cog"></i>
                        </button>
                    </div>
                </div>
                
                <div class="card-body p-0 position-relative" style="height: 500px;">
                    <!-- Loading indicator -->
                    <div id="chat-loading" class="position-absolute top-50 start-50 translate-middle">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading chat...</span>
                        </div>
                    </div>
                    
                    <!-- Chat messages container -->
                    <div id="chat-messages" class="p-3 overflow-auto" style="height: 100%; display: none;">
                        <!-- Messages will be loaded here -->
                    </div>
                    
                    <!-- Typing indicator -->
                    <div id="typing-indicator" class="position-absolute bottom-60 start-0 w-100 px-3" style="display: none;">
                        <div class="typing-indicator bg-light p-2 rounded">
                            <small class="text-muted"><span id="typing-users"></span> is typing...</small>
                            <div class="typing-dots">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Message input -->
                <div class="card-footer">
                    <form id="message-form" class="d-flex">
                        <input type="hidden" id="room-id" value="<?php echo $currentRoomId; ?>">
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-secondary" id="emoji-picker-btn">
                                <i class="far fa-smile"></i>
                            </button>
                            <input type="text" id="message-input" class="form-control" 
                                   placeholder="Type your message..." autocomplete="off" autofocus>
                            <button type="button" class="btn btn-outline-primary" id="send-message">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                    <div class="d-flex justify-content-between mt-2">
                        <small class="text-muted">Press Enter to send, Shift+Enter for new line</small>
                        <small class="text-muted" id="connection-status">Connecting...</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Room Modal -->
<div class="modal fade" id="createRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Chat Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="create-room-form">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="room-name" class="form-label">Room Name</label>
                        <input type="text" class="form-control" id="room-name" required>
                    </div>
                    <div class="mb-3">
                        <label for="room-description" class="form-label">Description</label>
                        <textarea class="form-control" id="room-description" rows="3"></textarea>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="room-is-public" checked>
                        <label class="form-check-label" for="room-is-public">Public Room</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Room</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Emoji Picker (using emoji-picker-element) -->
<emoji-picker id="emoji-picker"></emoji-picker>

<!-- Chat JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/emoji-picker-element@1.12.0"></script>
<script>
// Configuration
const config = {
    websocketUrl: 'ws://localhost:8080',
    currentUser: {
        id: '<?php echo $currentUser['id']; ?>',
        name: '<?php echo addslashes($currentUser['name']); ?>'
    },
    roomId: <?php echo $currentRoomId; ?>
};

// DOM Elements
const chatMessages = document.getElementById('chat-messages');
const messageInput = document.getElementById('message-input');
const sendButton = document.getElementById('send-message');
const messageForm = document.getElementById('message-form');
const chatLoading = document.getElementById('chat-loading');
const typingIndicator = document.getElementById('typing-indicator');
const typingUsers = document.getElementById('typing-users');
const userList = document.getElementById('user-list');
const onlineCount = document.getElementById('online-count');
const connectionStatus = document.getElementById('connection-status');
const emojiPicker = document.getElementById('emoji-picker');
const emojiPickerBtn = document.getElementById('emoji-picker-btn');

// WebSocket connection
let socket;
let reconnectAttempts = 0;
const maxReconnectAttempts = 5;
let reconnectTimeout;
let typingTimeout;
let isTyping = false;

// Initialize the chat
function initChat() {
    connectWebSocket();
    setupEventListeners();
    loadMessageHistory();
    setupEmojiPicker();
}

// Connect to WebSocket server
function connectWebSocket() {
    try {
        socket = new WebSocket(config.websocketUrl);
        
        socket.onopen = function() {
            console.log('WebSocket connection established');
            connectionStatus.textContent = 'Connected';
            connectionStatus.className = 'text-success';
            reconnectAttempts = 0;
            
            // Authenticate with the server
            authenticate();
        };
        
        socket.onmessage = function(event) {
            handleMessage(JSON.parse(event.data));
        };
        
        socket.onclose = function(event) {
            console.log('WebSocket connection closed', event);
            connectionStatus.textContent = 'Disconnected';
            connectionStatus.className = 'text-danger';
            
            // Try to reconnect
            if (reconnectAttempts < maxReconnectAttempts) {
                const delay = Math.min(1000 * Math.pow(2, reconnectAttempts), 30000); // Exponential backoff
                console.log(`Reconnecting in ${delay}ms...`);
                connectionStatus.textContent = `Reconnecting in ${delay/1000}s...`;
                
                reconnectTimeout = setTimeout(() => {
                    reconnectAttempts++;
                    connectWebSocket();
                }, delay);
            } else {
                connectionStatus.textContent = 'Disconnected. Please refresh the page.';
            }
        };
        
        socket.onerror = function(error) {
            console.error('WebSocket error:', error);
            connectionStatus.textContent = 'Connection error';
            connectionStatus.className = 'text-danger';
        };
        
    } catch (error) {
        console.error('Error initializing WebSocket:', error);
        connectionStatus.textContent = 'Connection failed';
        connectionStatus.className = 'text-danger';
    }
}

// Authenticate with the WebSocket server
function authenticate() {
    if (socket.readyState === WebSocket.OPEN) {
        const authMessage = {
            action: 'auth',
            user_id: config.currentUser.id,
            name: config.currentUser.name,
            token: '<?php echo generateCSRFToken(); ?>'
        };
        socket.send(JSON.stringify(authMessage));
    }
}

// Handle incoming messages
function handleMessage(data) {
    console.log('Received message:', data);
    
    switch (data.type) {
        case 'message':
            addMessageToChat(data);
            break;
            
        case 'message_history':
            displayMessageHistory(data.messages);
            break;
            
        case 'typing':
            handleTypingIndicator(data);
            break;
            
        case 'user_list':
            updateUserList(data.users);
            break;
            
        case 'error':
            showAlert(data.message, 'danger');
            break;
            
        default:
            console.log('Unknown message type:', data.type);
    }
}

// Send a message
function sendMessage() {
    const message = messageInput.value.trim();
    if (!message) return;
    
    if (socket.readyState === WebSocket.OPEN) {
        const messageData = {
            action: 'message',
            room_id: config.roomId,
            message: message
        };
        
        socket.send(JSON.stringify(messageData));
        messageInput.value = '';
        updateTypingStatus(false);
    } else {
        showAlert('Not connected to the chat server', 'danger');
    }
}

// Add a message to the chat UI
function addMessageToChat(message) {
    const messageElement = createMessageElement(message);
    chatMessages.appendChild(messageElement);
    scrollToBottom();
}

// Create a message element
function createMessageElement(message) {
    const isCurrentUser = message.user_id == config.currentUser.id;
    const messageTime = new Date(message.timestamp * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `message mb-3 ${isCurrentUser ? 'text-end' : ''}`;
    
    messageDiv.innerHTML = `
        <div class="d-flex ${isCurrentUser ? 'justify-content-end' : 'justify-content-start'}">
            <div class="message-bubble ${isCurrentUser ? 'bg-primary text-white' : 'bg-light'}">
                ${!isCurrentUser ? `<div class="message-sender">${escapeHtml(message.user_name)}</div>` : ''}
                <div class="message-content">${formatMessage(message.message)}</div>
                <div class="message-time">${messageTime}</div>
            </div>
        </div>
    `;
    
    return messageDiv;
}

// Load message history
function loadMessageHistory() {
    // In a real app, you would fetch this from your API
    // For now, we'll just show a loading state
    chatLoading.style.display = 'flex';
    chatMessages.style.display = 'none';
    
    // Simulate loading
    setTimeout(() => {
        chatLoading.style.display = 'none';
        chatMessages.style.display = 'block';
        scrollToBottom();
    }, 1000);
}

// Display message history
function displayMessageHistory(messages) {
    chatMessages.innerHTML = '';
    
    if (messages.length === 0) {
        chatMessages.innerHTML = `
            <div class="text-center text-muted py-5">
                <i class="fas fa-comments fa-3x mb-3"></i>
                <p>No messages yet. Be the first to say hello!</p>
            </div>
        `;
    } else {
        messages.forEach(message => {
            addMessageToChat(message);
        });
    }
    
    chatLoading.style.display = 'none';
    chatMessages.style.display = 'block';
    scrollToBottom();
}

// Update typing status
function updateTypingStatus(isTyping) {
    if (isTyping !== isTyping) {
        isTyping = isTyping;
        
        if (socket.readyState === WebSocket.OPEN) {
            const typingData = {
                action: 'typing',
                room_id: config.roomId,
                is_typing: isTyping
            };
            
            socket.send(JSON.stringify(typingData));
        }
    }
    
    // Clear previous timeout
    if (typingTimeout) {
        clearTimeout(typingTimeout);
    }
    
    // Set a timeout to automatically set typing to false after 2 seconds of inactivity
    if (isTyping) {
        typingTimeout = setTimeout(() => {
            updateTypingStatus(false);
        }, 2000);
    }
}

// Handle typing indicator
function handleTypingIndicator(data) {
    if (data.user_id === config.currentUser.id) return;
    
    if (data.is_typing) {
        typingUsers.textContent = data.user_name;
        typingIndicator.style.display = 'block';
    } else {
        typingIndicator.style.display = 'none';
    }
}

// Update user list
function updateUserList(users) {
    if (!users || users.length === 0) {
        userList.innerHTML = '<div class="text-center py-3 text-muted">No users online</div>';
        onlineCount.textContent = '0';
        return;
    }
    
    let html = '';
    users.forEach(user => {
        html += `
            <div class="list-group-item list-group-item-action">
                <div class="d-flex align-items-center">
                    <div class="position-relative me-2">
                        <div class="avatar-sm">
                            <span class="avatar-title rounded-circle bg-primary text-white">
                                ${user.name.charAt(0).toUpperCase()}
                            </span>
                        </div>
                        <span class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-2 border-white" 
                              style="width: 10px; height: 10px;"></span>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0">${escapeHtml(user.name)}</h6>
                        <small class="text-muted">Online</small>
                    </div>
                </div>
            </div>
        `;
    });
    
    userList.innerHTML = html;
    onlineCount.textContent = users.length;
}

// Setup event listeners
function setupEventListeners() {
    // Send message on form submit
    messageForm.addEventListener('submit', function(e) {
        e.preventDefault();
        sendMessage();
    });
    
    // Send message on button click
    sendButton.addEventListener('click', sendMessage);
    
    // Handle typing indicator
    messageInput.addEventListener('input', function() {
        if (!isTyping) {
            updateTypingStatus(true);
        }
    });
    
    // Handle Enter key (send on Enter, new line on Shift+Enter)
    messageInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', scrollToBottom);
    
    // Handle beforeunload
    window.addEventListener('beforeunload', function() {
        if (socket) {
            socket.close();
        }
    });
}

// Setup emoji picker
function setupEmojiPicker() {
    emojiPickerBtn.addEventListener('click', function() {
        emojiPicker.style.display = emojiPicker.style.display === 'none' ? 'block' : 'none';
    });
    
    emojiPicker.addEventListener('emoji-click', event => {
        const cursorPos = messageInput.selectionStart;
        const text = messageInput.value;
        const before = text.substring(0, cursorPos);
        const after = text.substring(cursorPos);
        
        messageInput.value = before + event.detail.unicode + after;
        messageInput.focus();
        messageInput.selectionStart = messageInput.selectionEnd = cursorPos + event.detail.unicode.length;
        
        // Hide the picker after selection
        emojiPicker.style.display = 'none';
    });
    
    // Hide emoji picker when clicking outside
    document.addEventListener('click', function(event) {
        if (!emojiPicker.contains(event.target) && event.target !== emojiPickerBtn) {
            emojiPicker.style.display = 'none';
        }
    });
}

// Helper functions
function scrollToBottom() {
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function formatMessage(text) {
    // Convert URLs to links
    text = text.replace(
        /(https?:\/\/[^\s]+)/g, 
        '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>'
    );
    
    // Convert newlines to <br>
    text = text.replace(/\n/g, '<br>');
    
    return text;
}

function showAlert(message, type = 'info') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alert.role = 'alert';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(alert);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

// Initialize the chat when the DOM is loaded
document.addEventListener('DOMContentLoaded', initChat);
</script>

<style>
/* Chat styles */
.message-bubble {
    max-width: 70%;
    padding: 10px 15px;
    border-radius: 18px;
    margin-bottom: 8px;
    position: relative;
    word-wrap: break-word;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.message-sender {
    font-weight: 600;
    font-size: 0.8rem;
    margin-bottom: 2px;
}

.message-content {
    margin: 5px 0;
    line-height: 1.4;
}

.message-time {
    font-size: 0.7rem;
    opacity: 0.8;
    text-align: right;
}

/* Typing indicator */
.typing-indicator {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 15px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.typing-dots {
    display: inline-flex;
    align-items: center;
    height: 17px;
}

.typing-dots span {
    width: 6px;
    height: 6px;
    background-color: #6c757d;
    border-radius: 50%;
    display: inline-block;
    margin: 0 2px;
    animation: typing-dots 1.4s infinite ease-in-out both;
}

.typing-dots span:nth-child(1) {
    animation-delay: -0.32s;
}

.typing-dots span:nth-child(2) {
    animation-delay: -0.16s;
}

@keyframes typing-dots {
    0%, 80%, 100% { 
        transform: scale(0);
    } 40% { 
        transform: scale(1.0);
    }
}

/* Emoji picker */
#emoji-picker {
    position: absolute;
    bottom: 60px;
    right: 20px;
    z-index: 1000;
    display: none;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

/* Custom scrollbar */
#chat-messages::-webkit-scrollbar {
    width: 6px;
}

#chat-messages::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

#chat-messages::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

#chat-messages::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .message-bubble {
        max-width: 85%;
    }
    
    .col-md-3, .col-md-9 {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
}
</style>

<?php
// Include footer
include __DIR__ . '/../includes/footer.php';
?>
