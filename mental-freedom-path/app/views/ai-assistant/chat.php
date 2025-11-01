<?php $this->layout('layouts/app', ['title' => 'AI Wellness Assistant - Mental Freedom Path']) ?>

<div class="ai-assistant-container">
    <div class="ai-chat-container">
        <!-- Sidebar -->
        <div class="ai-sidebar">
            <div class="ai-sidebar-header">
                <button class="btn btn-primary btn-sm btn-block" id="new-chat-btn">
                    <i class="fas fa-plus"></i> New Chat
                </button>
            </div>
            <div class="ai-conversation-list" id="conversation-list">
                <!-- Conversations will be loaded here -->
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Chat Area -->
        <div class="ai-chat-area">
            <!-- Chat Header -->
            <div class="ai-chat-header">
                <div class="d-flex align-items-center">
                    <button class="btn btn-link d-md-none mr-2" id="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h5 class="mb-0">
                        <i class="fas fa-robot text-primary mr-2"></i>
                        <span id="conversation-title">New Chat</span>
                        <small class="text-muted d-block d-md-inline-block ml-md-3" id="conversation-status">
                            <i class="fas fa-circle text-success"></i> Online
                        </small>
                    </h5>
                </div>
                <div class="dropdown">
                    <button class="btn btn-link text-muted" type="button" id="chat-options" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="chat-options">
                        <a class="dropdown-item" href="#" id="rename-chat">
                            <i class="fas fa-edit fa-fw mr-2"></i>Rename Chat
                        </a>
                        <a class="dropdown-item" href="#" id="delete-chat">
                            <i class="fas fa-trash-alt fa-fw mr-2"></i>Delete Chat
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" id="export-chat">
                            <i class="fas fa-file-export fa-fw mr-2"></i>Export Chat
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Messages Container -->
            <div class="ai-messages" id="chat-messages">
                <!-- Welcome message -->
                <div class="message ai-welcome-message">
                    <div class="message-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="message-content">
                        <div class="message-sender">Mental Health Assistant</div>
                        <div class="message-text">
                            <p>Hello! I'm your AI Wellness Assistant. I'm here to provide support and information about mental health.</p>
                            <p>You can ask me about:</p>
                            <ul>
                                <li>Coping strategies for stress and anxiety</li>
                                <li>Mental health resources</li>
                                <li>Self-care techniques</li>
                                <li>And much more</li>
                            </ul>
                            <p>How can I help you today?</p>
                        </div>
                        <div class="message-time">Just now</div>
                    </div>
                </div>
                
                <!-- Messages will be loaded here -->
            </div>
            
            <!-- Suggested Resources -->
            <div class="suggested-resources" id="suggested-resources">
                <div class="suggested-resources-header">
                    <span>Suggested Resources</span>
                </div>
                <div class="suggested-resources-container" id="suggested-resources-container">
                    <!-- Resources will be loaded here -->
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary spinner-border-sm" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Input Area -->
            <div class="ai-input-container">
                <form id="chat-form">
                    <input type="hidden" name="_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="conversation_id" id="conversation-id" value="<?= $conversation->id ?? '' ?>">
                    
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button class="btn btn-outline-secondary" type="button" id="attach-file">
                                <i class="far fa-paperclip"></i>
                            </button>
                        </div>
                        
                        <input type="text" 
                               class="form-control" 
                               id="message-input" 
                               placeholder="Type your message..." 
                               autocomplete="off"
                               aria-label="Type your message"">
                        
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit" id="send-message">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="typing-indicator d-none" id="ai-typing">
                        <span></span><span></span><span></span>
                    </div>
                </form>
                
                <div class="text-muted small mt-2 text-center">
                    Mental Freedom Path AI Assistant - Your conversations are confidential and secure
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rename Chat Modal -->
<div class="modal fade" id="renameChatModal" tabindex="-1" role="dialog" aria-labelledby="renameChatModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="renameChatModalLabel">Rename Chat</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="rename-chat-form">
                    <div class="form-group">
                        <label for="chat-title">Chat Title</label>
                        <input type="text" class="form-control" id="chat-title" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-chat-title">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteChatModal" tabindex="-1" role="dialog" aria-labelledby="deleteChatModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger" id="deleteChatModalLabel">Delete Chat</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this chat? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-chat">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Crisis Resources Modal -->
<div class="modal fade" id="crisisResourcesModal" tabindex="-1" role="dialog" aria-labelledby="crisisResourcesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="crisisResourcesModalLabel">Crisis Support</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="crisis-resources-content">
                <!-- Crisis resources will be loaded here -->
                <div class="text-center py-4">
                    <div class="spinner-border text-danger" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php $this->push('styles') ?>
<style>
/* AI Chat Container */
.ai-assistant-container {
    height: calc(100vh - 120px);
    background-color: #f8f9fa;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

/* Sidebar */
.ai-chat-container {
    display: flex;
    height: 100%;
}

.ai-sidebar {
    width: 280px;
    background-color: #fff;
    border-right: 1px solid #e9ecef;
    display: flex;
    flex-direction: column;
    transition: transform 0.3s ease;
    z-index: 10;
}

.ai-sidebar-header {
    padding: 15px;
    border-bottom: 1px solid #e9ecef;
}

.ai-conversation-list {
    flex: 1;
    overflow-y: auto;
    padding: 10px 0;
}

.conversation-item {
    padding: 10px 15px;
    cursor: pointer;
    border-left: 3px solid transparent;
    transition: all 0.2s;
    display: flex;
    align-items: center;
}

.conversation-item:hover {
    background-color: #f8f9fa;
}

.conversation-item.active {
    background-color: #e9f5ff;
    border-left-color: #007bff;
    font-weight: 500;
}

.conversation-item .truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex: 1;
}

.conversation-item .badge {
    font-size: 10px;
    margin-left: 5px;
}

/* Main Chat Area */
.ai-chat-area {
    flex: 1;
    display: flex;
    flex-direction: column;
    background-color: #f0f2f5;
    position: relative;
}

.ai-chat-header {
    padding: 15px 20px;
    background-color: #fff;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 5;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.ai-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background-color: #f0f2f5;
    background-image: url('/assets/images/chat-bg-pattern.png');
    background-size: 400px;
    background-attachment: fixed;
    opacity: 0.95;
}

/* Message Styling */
.message {
    display: flex;
    margin-bottom: 20px;
    max-width: 85%;
    position: relative;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.message.user {
    margin-left: auto;
    flex-direction: row-reverse;
}

.message-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin: 0 10px;
    color: #6c757d;
    font-size: 18px;
}

.message.user .message-avatar {
    background-color: #007bff;
    color: white;
}

.message-content {
    max-width: calc(100% - 60px);
}

.message.user .message-content {
    text-align: right;
}

.message-sender {
    font-weight: 600;
    font-size: 0.85rem;
    margin-bottom: 3px;
    color: #495057;
}

.message.user .message-sender {
    color: #007bff;
}

.message-text {
    background-color: white;
    padding: 12px 15px;
    border-radius: 15px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    word-wrap: break-word;
    line-height: 1.5;
}

.message.user .message-text {
    background-color: #007bff;
    color: white;
    border-top-right-radius: 5px;
}

.message:not(.user) .message-text {
    border-top-left-radius: 5px;
}

.message-time {
    font-size: 0.7rem;
    color: #6c757d;
    margin-top: 5px;
    padding: 0 5px;
}

/* AI Welcome Message */
.ai-welcome-message {
    max-width: 80%;
    margin: 20px auto 40px;
    text-align: center;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
}

.ai-welcome-message .message-text {
    background: none;
    padding: 0;
    box-shadow: none;
    text-align: left;
}

.ai-welcome-message .message-avatar {
    width: 50px;
    height: 50px;
    margin: 0 auto 15px;
    font-size: 24px;
    background: linear-gradient(135deg, #4e73df, #224abe);
    color: white;
}

/* Suggested Resources */
.suggested-resources {
    background-color: #fff;
    border-top: 1px solid #e9ecef;
    padding: 15px;
    max-height: 150px;
    overflow-y: auto;
    transition: all 0.3s ease;
}

.suggested-resources.collapsed {
    max-height: 0;
    padding: 0;
    border: none;
    overflow: hidden;
}

.suggested-resources-header {
    font-size: 0.8rem;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.suggested-resources-container {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    padding-bottom: 5px;
}

.resource-card {
    min-width: 200px;
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 12px;
    border: 1px solid #e9ecef;
    transition: all 0.2s;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    display: block;
}

.resource-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    text-decoration: none;
    color: inherit;
}

.resource-card .resource-title {
    font-weight: 600;
    margin-bottom: 5px;
    font-size: 0.9rem;
}

.resource-card .resource-description {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 8px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.resource-card .resource-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.7rem;
    color: #6c757d;
}

/* Input Area */
.ai-input-container {
    background-color: #fff;
    padding: 15px 20px;
    border-top: 1px solid #e9ecef;
    position: relative;
}

#chat-form {
    position: relative;
}

#message-input {
    border-radius: 20px;
    padding: 10px 20px;
    border: 1px solid #ced4da;
    font-size: 0.95rem;
    transition: all 0.3s;
}

#message-input:focus {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
    border-color: #80bdff;
}

#send-message {
    border-top-right-radius: 20px;
    border-bottom-right-radius: 20px;
    padding: 10px 20px;
}

/* Typing Indicator */
.typing-indicator {
    display: flex;
    padding: 10px 15px;
    background-color: #f8f9fa;
    border-radius: 15px;
    position: absolute;
    bottom: 100%;
    left: 15px;
    margin-bottom: 10px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.typing-indicator span {
    height: 8px;
    width: 8px;
    background-color: #6c757d;
    border-radius: 50%;
    display: inline-block;
    margin: 0 2px;
    opacity: 0.4;
}

.typing-indicator span:nth-child(1) {
    animation: typing 1s infinite;
}

.typing-indicator span:nth-child(2) {
    animation: typing 1s infinite 0.2s;
}

.typing-indicator span:nth-child(3) {
    animation: typing 1s infinite 0.4s;
}

@keyframes typing {
    0% { opacity: 0.4; transform: translateY(0); }
    50% { opacity: 1; transform: translateY(-5px); }
    100% { opacity: 0.4; transform: translateY(0); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .ai-sidebar {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        transform: translateX(-100%);
        z-index: 1050;
        box-shadow: 3px 0 15px rgba(0, 0, 0, 0.1);
    }
    
    .ai-sidebar.show {
        transform: translateX(0);
    }
    
    .message {
        max-width: 90%;
    }
    
    .ai-welcome-message {
        max-width: 95%;
    }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    .ai-assistant-container,
    .ai-chat-area,
    .ai-messages {
        background-color: #1a1a1a;
        color: #e0e0e0;
    }
    
    .ai-sidebar,
    .ai-chat-header,
    .ai-input-container,
    .suggested-resources {
        background-color: #2d2d2d;
        border-color: #3d3d3d;
    }
    
    .message-text {
        background-color: #3d3d3d;
        color: #e0e0e0;
    }
    
    .message.user .message-text {
        background-color: #0d6efd;
    }
    
    .ai-welcome-message {
        background: linear-gradient(135deg, #2d2d2d, #3d3d3d);
    }
    
    .resource-card {
        background-color: #3d3d3d;
        border-color: #4d4d4d;
    }
    
    #message-input {
        background-color: #3d3d3d;
        border-color: #4d4d4d;
        color: #e0e0e0;
    }
    
    #message-input::placeholder {
        color: #8d8d8d;
    }
    
    .typing-indicator {
        background-color: #3d3d3d;
    }
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.3s ease-out forwards;
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Dark mode scrollbar */
@media (prefers-color-scheme: dark) {
    ::-webkit-scrollbar-track {
        background: #2d2d2d;
    }
    
    ::-webkit-scrollbar-thumb {
        background: #555;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: #666;
    }
}
</style>
<?php $this->end() ?>

<?php $this->push('scripts') ?>
<script>
$(document).ready(function() {
    // Initialize variables
    let currentConversationId = $('#conversation-id').val();
    let isProcessing = false;
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Load conversations
    function loadConversations() {
        $.ajax({
            url: '/ai-assistant/conversations',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    renderConversations(response.conversations);
                    
                    // If no active conversation, select the first one or create new
                    if (!currentConversationId && response.conversations.length > 0) {
                        loadConversation(response.conversations[0].id);
                    } else if (response.conversations.length === 0) {
                        createNewConversation();
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading conversations:', error);
                showAlert('danger', 'Failed to load conversations. Please try again.');
            }
        });
    }
    
    // Render conversations list
    function renderConversations(conversations) {
        const $container = $('#conversation-list');
        $container.empty();
        
        if (conversations.length === 0) {
            $container.html(`
                <div class="text-center p-4">
                    <p class="text-muted">No conversations yet</p>
                    <button class="btn btn-sm btn-primary" id="create-first-chat">
                        Start a New Chat
                    </button>
                </div>
            `);
            return;
        }
        
        conversations.forEach(convo => {
            const isActive = convo.id.toString() === currentConversationId;
            const activeClass = isActive ? 'active' : '';
            const unreadBadge = convo.unread_count > 0 ? 
                `<span class="badge badge-primary">${convo.unread_count}</span>` : '';
            
            $container.append(`
                <div class="conversation-item ${activeClass}" data-id="${convo.id}">
                    <div class="truncate" title="${escapeHtml(convo.title)}">
                        <i class="far fa-comment-alt fa-fw mr-2"></i>
                        ${escapeHtml(convo.title)}
                    </div>
                    ${unreadBadge}
                </div>
            `);
        });
    }
    
    // Load a specific conversation
    function loadConversation(conversationId) {
        if (!conversationId) return;
        
        currentConversationId = conversationId;
        $('#conversation-id').val(conversationId);
        
        // Update active state in the UI
        $('.conversation-item').removeClass('active');
        $(`.conversation-item[data-id="${conversationId}"]`).addClass('active');
        
        // Show loading state
        const $messagesContainer = $('#chat-messages');
        $messagesContainer.html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading conversation...</p>
            </div>
        `);
        
        // Load conversation data
        $.ajax({
            url: `/ai-assistant/conversation/${conversationId}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    renderMessages(response.messages);
                    updateConversationTitle(response.conversation.title);
                    scrollToBottom();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading conversation:', error);
                showAlert('danger', 'Failed to load conversation. Please try again.');
            }
        });
    }
    
    // Render messages in the chat
    function renderMessages(messages) {
        const $container = $('#chat-messages');
        $container.empty();
        
        if (messages.length === 0) {
            // Show welcome message if no messages
            $container.html(`
                <div class="message ai-welcome-message">
                    <div class="message-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="message-content">
                        <div class="message-sender">Mental Health Assistant</div>
                        <div class="message-text">
                            <p>Hello! I'm your AI Wellness Assistant. I'm here to provide support and information about mental health.</p>
                            <p>How can I help you today?</p>
                        </div>
                        <div class="message-time">Just now</div>
                    </div>
                </div>
            `);
            return;
        }
        
        messages.forEach((msg, index) => {
            const isUser = msg.sender === 'user';
            const messageClass = isUser ? 'user' : 'assistant';
            const messageTime = formatTime(msg.created_at);
            
            // Add typing animation for the last AI message
            const typingClass = (!isUser && index === messages.length - 1) ? 'typing' : '';
            
            const $message = $(`
                <div class="message ${messageClass} animate-fade-in-up" style="animation-delay: ${index * 0.05}s">
                    <div class="message-avatar">
                        ${isUser ? '<i class="fas fa-user"></i>' : '<i class="fas fa-robot"></i>'}
                    </div>
                    <div class="message-content">
                        <div class="message-sender">
                            ${isUser ? 'You' : 'Mental Health Assistant'}
                        </div>
                        <div class="message-text ${typingClass}">
                            ${formatMessageText(msg.message)}
                        </div>
                        <div class="message-time">${messageTime}</div>
                    </div>
                </div>
            `);
            
            $container.append($message);
            
            // Type out the message if it's the last AI message
            if (typingClass) {
                typeMessage($message.find('.message-text'), msg.message);
            }
        });
    }
    
    // Format message text (newlines, links, etc.)
    function formatMessageText(text) {
        if (!text) return '';
        
        // Replace newlines with <br>
        let formatted = text.replace(/\n/g, '<br>');
        
        // Simple URL detection
        const urlRegex = /(https?:\/\/[^\s]+)/g;
        formatted = formatted.replace(urlRegex, function(url) {
            return `<a href="${url}" target="_blank" rel="noopener noreferrer">${url}</a>`;
        });
        
        return formatted;
    }
    
    // Type out message with typing effect
    function typeMessage($element, text, speed = 20) {
        $element.html('');
        let i = 0;
        
        function typeWriter() {
            if (i < text.length) {
                // Get the next character
                let char = text.charAt(i);
                
                // Handle HTML tags
                if (char === '<') {
                    // Find the end of the tag
                    const tagEnd = text.indexOf('>', i);
                    if (tagEnd !== -1) {
                        // Add the entire tag at once
                        const tag = text.substring(i, tagEnd + 1);
                        $element.append(tag);
                        i = tagEnd + 1;
                        setTimeout(typeWriter, speed);
                        return;
                    }
                }
                
                // Add the character
                $element.append(char);
                i++;
                
                // Scroll to bottom after each line
                if (char === '\n' || i % 50 === 0) {
                    scrollToBottom();
                }
                
                setTimeout(typeWriter, speed);
            } else {
                // Done typing
                scrollToBottom();
                
                // Show suggested resources after the last message is fully typed
                if ($element.closest('.message').is(':last-child')) {
                    loadSuggestedResources();
                }
            }
        }
        
        typeWriter();
    }
    
    // Send a message
    function sendMessage(message) {
        if (isProcessing || !message.trim()) return;
        
        isProcessing = true;
        
        // Add user message to the UI immediately
        const messageId = 'msg-' + Date.now();
        const messageTime = formatTime(new Date().toISOString());
        
        const $messagesContainer = $('#chat-messages');
        $messagesContainer.append(`
            <div class="message user" id="${messageId}">
                <div class="message-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="message-content">
                    <div class="message-sender">You</div>
                    <div class="message-text">${escapeHtml(message)}</div>
                    <div class="message-time">${messageTime}</div>
                </div>
            </div>
        `);
        
        // Show typing indicator
        $messagesContainer.append(`
            <div class="message" id="typing-indicator">
                <div class="message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    <div class="message-sender">Mental Health Assistant</div>
                    <div class="typing-indicator">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            </div>
        `);
        
        // Scroll to bottom
        scrollToBottom();
        
        // Clear input
        $('#message-input').val('');
        
        // Send to server
        $.ajax({
            url: '/ai-assistant/send-message',
            method: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                conversation_id: currentConversationId,
                message: message
            },
            success: function(response) {
                if (response.success) {
                    // Remove typing indicator
                    $('#typing-indicator').remove();
                    
                    // Add AI response
                    const responseId = 'msg-' + Date.now();
                    const responseTime = formatTime(new Date().toISOString());
                    
                    const $response = $(`
                        <div class="message" id="${responseId}">
                            <div class="message-avatar">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div class="message-content">
                                <div class="message-sender">Mental Health Assistant</div>
                                <div class="message-text">
                                    ${formatMessageText(response.response.text)}
                                </div>
                                <div class="message-time">${responseTime}</div>
                            </div>
                        </div>
                    `);
                    
                    $messagesContainer.append($response);
                    
                    // Type out the response
                    const $responseText = $response.find('.message-text');
                    const responseText = response.response.text;
                    $responseText.html('');
                    
                    typeMessage($responseText, responseText);
                    
                    // Update conversation title if this is the first message
                    if (response.conversation_title) {
                        updateConversationTitle(response.conversation_title);
                    }
                    
                    // Update conversation list
                    loadConversations();
                    
                    // Show suggested resources if any
                    if (response.suggested_resources && response.suggested_resources.length > 0) {
                        renderSuggestedResources(response.suggested_resources);
                    }
                    
                    // Check for crisis response
                    if (response.crisis_detected) {
                        showCrisisResources();
                    }
                } else {
                    showError('Failed to send message. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error sending message:', error);
                showError('An error occurred. Please try again.');
                
                // Remove typing indicator
                $('#typing-indicator').remove();
            },
            complete: function() {
                isProcessing = false;
            }
        });
    }
    
    // Load suggested resources
    function loadSuggestedResources() {
        if (!currentConversationId) return;
        
        $.ajax({
            url: `/ai-assistant/conversation/${currentConversationId}/suggestions`,
            method: 'GET',
            success: function(response) {
                if (response.success && response.resources.length > 0) {
                    renderSuggestedResources(response.resources);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading suggested resources:', error);
            }
        });
    }
    
    // Render suggested resources
    function renderSuggestedResources(resources) {
        const $container = $('#suggested-resources-container');
        $container.empty();
        
        if (!resources || resources.length === 0) {
            $('#suggested-resources').addClass('d-none');
            return;
        }
        
        resources.forEach(resource => {
            $container.append(`
                <a href="${escapeHtml(resource.url)}" class="resource-card" target="_blank" rel="noopener noreferrer">
                    <div class="resource-title">${escapeHtml(resource.title)}</div>
                    <div class="resource-description">${truncate(escapeHtml(resource.description || ''), 100)}</div>
                    <div class="resource-meta">
                        <span class="badge badge-light">${escapeHtml(resource.category || 'Resource')}</span>
                        <small>${escapeHtml(resource.resource_type || 'Article')}</small>
                    </div>
                </a>
            `);
        });
        
        $('#suggested-resources').removeClass('d-none');
    }
    
    // Create a new conversation
    function createNewConversation() {
        $.ajax({
            url: '/ai-assistant/conversations',
            method: 'POST',
            data: {
                _token: $('input[name="_token"]').val()
            },
            success: function(response) {
                if (response.success) {
                    currentConversationId = response.conversation_id;
                    $('#conversation-id').val(currentConversationId);
                    loadConversation(currentConversationId);
                    loadConversations();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error creating conversation:', error);
                showError('Failed to create a new conversation. Please try again.');
            }
        });
    }
    
    // Update conversation title
    function updateConversationTitle(title) {
        if (!title) return;
        
        $('#conversation-title').text(title);
        
        // Update in the conversations list
        $(`.conversation-item[data-id="${currentConversationId}"] .truncate`)
            .attr('title', title)
            .html(`<i class="far fa-comment-alt fa-fw mr-2"></i>${escapeHtml(title)}`);
    }
    
    // Show crisis resources
    function showCrisisResources() {
        // Show modal with crisis resources
        $.get('/ai-assistant/crisis-resources', function(response) {
            $('#crisis-resources-content').html(response);
            $('#crisisResourcesModal').modal('show');
        }).fail(function() {
            // Fallback to default crisis resources
            const defaultResources = `
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-triangle"></i> Immediate Help Available</h5>
                    <p>If you're in crisis or having thoughts of self-harm, please reach out to these resources immediately:</p>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <strong>National Suicide Prevention Lifeline</strong><br>
                            <i class="fas fa-phone-alt mr-2"></i> <a href="tel:988">988</a> or <a href="tel:1-800-273-8255">1-800-273-8255</a><br>
                            <small class="text-muted">Available 24/7 for free and confidential support</small>
                        </li>
                        <li class="mb-2">
                            <strong>Crisis Text Line</strong><br>
                            <i class="fas fa-comment-alt mr-2"></i> Text HOME to <a href="sms:741741">741741</a><br>
                            <small class="text-muted">Free 24/7 crisis support via text message</small>
                        </li>
                        <li class="mb-2">
                            <strong>Emergency Services</strong><br>
                            <i class="fas fa-ambulance mr-2"></i> Call <a href="tel:911">911</a> or go to the nearest emergency room
                        </li>
                    </ul>
                    <hr>
                    <p class="mb-0">You're not alone. Help is available, and recovery is possible.</p>
                </div>
            `;
            
            $('#crisis-resources-content').html(defaultResources);
            $('#crisisResourcesModal').modal('show');
        });
    }
    
    // Utility functions
    function formatTime(timestamp) {
        if (!timestamp) return '';
        
        const date = new Date(timestamp);
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    
    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe
            .toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    
    function truncate(str, maxLength) {
        if (!str) return '';
        return str.length > maxLength ? str.substring(0, maxLength) + '...' : str;
    }
    
    function scrollToBottom() {
        const $messages = $('.ai-messages');
        $messages.scrollTop($messages[0].scrollHeight);
    }
    
    function showError(message) {
        showAlert('danger', message);
    }
    
    function showAlert(type, message) {
        const $alert = $(`
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `);
        
        // Add to the top of the chat area
        $('.ai-chat-area').prepend($alert);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            $alert.alert('close');
        }, 5000);
    }
    
    // Event Listeners
    
    // Send message on form submit
    $('#chat-form').on('submit', function(e) {
        e.preventDefault();
        const message = $('#message-input').val().trim();
        if (message) {
            sendMessage(message);
        }
    });
    
    // New chat button
    $(document).on('click', '#new-chat-btn, #create-first-chat', function(e) {
        e.preventDefault();
        createNewConversation();
    });
    
    // Click on conversation item
    $(document).on('click', '.conversation-item', function() {
        const conversationId = $(this).data('id');
        if (conversationId) {
            loadConversation(conversationId);
        }
    });
    
    // Rename chat
    $('#rename-chat').on('click', function(e) {
        e.preventDefault();
        const currentTitle = $('#conversation-title').text();
        $('#chat-title').val(currentTitle);
        $('#renameChatModal').modal('show');
    });
    
    // Save chat title
    $('#save-chat-title').on('click', function() {
        const newTitle = $('#chat-title').val().trim();
        if (!newTitle) return;
        
        $.ajax({
            url: `/ai-assistant/conversation/${currentConversationId}/title`,
            method: 'PUT',
            data: {
                _token: $('input[name="_token"]').val(),
                title: newTitle
            },
            success: function(response) {
                if (response.success) {
                    updateConversationTitle(newTitle);
                    $('#renameChatModal').modal('hide');
                }
            },
            error: function() {
                showError('Failed to update chat title. Please try again.');
            }
        });
    });
    
    // Delete chat
    $('#delete-chat').on('click', function(e) {
        e.preventDefault();
        $('#deleteChatModal').modal('show');
    });
    
    // Confirm delete chat
    $('#confirm-delete-chat').on('click', function() {
        if (!currentConversationId) return;
        
        $.ajax({
            url: `/ai-assistant/conversation/${currentConversationId}`,
            method: 'DELETE',
            data: {
                _token: $('input[name="_token"]').val()
            },
            success: function(response) {
                if (response.success) {
                    $('#deleteChatModal').modal('hide');
                    createNewConversation();
                    loadConversations();
                }
            },
            error: function() {
                showError('Failed to delete chat. Please try again.');
            }
        });
    });
    
    // Toggle sidebar on mobile
    $('#sidebar-toggle').on('click', function() {
        $('.ai-sidebar').toggleClass('show');
    });
    
    // Close sidebar when clicking outside on mobile
    $(document).on('click', function(e) {
        if ($(window).width() <= 768 && 
            !$(e.target).closest('.ai-sidebar').length && 
            !$(e.target).closest('#sidebar-toggle').length) {
            $('.ai-sidebar').removeClass('show');
        }
    });
    
    // Initialize
    loadConversations();
    
    // Load initial conversation if ID is in URL
    const urlParams = new URLSearchParams(window.location.search);
    const conversationId = urlParams.get('conversation');
    if (conversationId) {
        loadConversation(conversationId);
    }
});
</script>
<?php $this->end() ?>
