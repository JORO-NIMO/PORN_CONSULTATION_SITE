class WellnessChat {
    constructor() {
        this.chatHistory = [];
        this.chatContainer = document.getElementById('wellness-chat-messages');
        this.inputField = document.getElementById('chat-input');
        this.sendButton = document.getElementById('send-message');
        this.typingIndicator = document.getElementById('typing-indicator');
        
        this.initialize();
    }
    
    initialize() {
        this.sendButton.addEventListener('click', () => this.sendMessage());
        this.inputField.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });
        
        // Load any previous chat history from localStorage
        this.loadChatHistory();
    }
    
    async sendMessage() {
        const message = this.inputField.value.trim();
        if (!message) return;
        
        // Add user message to chat
        this.addMessage('user', message);
        this.inputField.value = '';
        this.inputField.disabled = true;
        this.sendButton.disabled = true;
        
        // Show typing indicator
        this.showTypingIndicator(true);
        
        try {
            const response = await fetch('/api/chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: message,
                    context: this.chatHistory
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.addMessage('assistant', data.response);
                
                // Update chat history
                this.chatHistory.push(`User: ${message}`, `Assistant: ${data.response}`);
                
                // Keep chat history manageable
                if (this.chatHistory.length > 10) {
                    this.chatHistory = this.chatHistory.slice(-10);
                }
                
                // Save chat history
                this.saveChatHistory();
                
                // Show crisis resources if needed
                if (data.is_crisis) {
                    this.showCrisisResources();
                }
            } else {
                throw new Error(data.error || 'Failed to get response');
            }
            
        } catch (error) {
            console.error('Error:', error);
            this.addMessage('assistant', "I'm having trouble connecting right now. Please try again later.");
        } finally {
            this.showTypingIndicator(false);
            this.inputField.disabled = false;
            this.sendButton.disabled = false;
            this.inputField.focus();
        }
    }
    
    addMessage(role, content) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${role}`;
        
        // Format the message with line breaks
        const formattedContent = content.replace(/\n/g, '<br>');
        messageDiv.innerHTML = formattedContent;
        
        this.chatContainer.appendChild(messageDiv);
        this.chatContainer.scrollTop = this.chatContainer.scrollHeight;
    }
    
    showTypingIndicator(show) {
        if (show) {
            this.typingIndicator.style.display = 'flex';
        } else {
            this.typingIndicator.style.display = 'none';
        }
        this.chatContainer.scrollTop = this.chatContainer.scrollHeight;
    }
    
    showCrisisResources() {
        const resources = `
            <div class="crisis-alert">
                <h4>Important Resources</h4>
                <p>If you're in crisis, please contact:</p>
                <ul>
                    <li>National Suicide Prevention Lifeline: 1-800-273-8255</li>
                    <li>Crisis Text Line: Text HOME to 741741</li>
                    <li>Emergency Services: 911</li>
                </ul>
                <p>You're not alone, and help is available.</p>
            </div>
        `;
        const alertDiv = document.createElement('div');
        alertDiv.className = 'crisis-alert-container';
        alertDiv.innerHTML = resources;
        this.chatContainer.appendChild(alertDiv);
        this.chatContainer.scrollTop = this.chatContainer.scrollHeight;
    }
    
    saveChatHistory() {
        try {
            localStorage.setItem('wellnessChatHistory', JSON.stringify(this.chatHistory));
        } catch (e) {
            console.warn('Could not save chat history:', e);
        }
    }
    
    loadChatHistory() {
        try {
            const savedHistory = localStorage.getItem('wellnessChatHistory');
            if (savedHistory) {
                this.chatHistory = JSON.parse(savedHistory);
                // Optionally, display previous messages
                // this.chatHistory.forEach((msg, index) => {
                //     const role = msg.startsWith('User:') ? 'user' : 'assistant';
                //     const content = msg.replace(/^(User|Assistant):\s*/, '');
                //     this.addMessage(role, content);
                // });
            }
        } catch (e) {
            console.warn('Could not load chat history:', e);
        }
    }
}

// Initialize chat when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.wellnessChat = new WellnessChat();
});
