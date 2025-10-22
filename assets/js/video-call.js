// WebRTC Video Call using Daily.co
(function() {
    let callFrame;
    let localStream;
    let isVideoEnabled = true;
    let isAudioEnabled = true;
    
    const localVideoElement = document.getElementById('localVideoElement');
    const remoteVideoElement = document.getElementById('remoteVideoElement');
    const toggleVideoBtn = document.getElementById('toggleVideo');
    const toggleAudioBtn = document.getElementById('toggleAudio');
    const endCallBtn = document.getElementById('endCall');
    const chatSidebar = document.getElementById('chatSidebar');
    const openChatBtn = document.getElementById('openChat');
    const toggleChatBtn = document.getElementById('toggleChat');
    const chatMessages = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInput');
    const sendChatBtn = document.getElementById('sendChat');
    
    // Initialize video call
    async function initCall() {
        try {
            // Get local media stream
            localStream = await navigator.mediaDevices.getUserMedia({
                video: true,
                audio: true
            });
            
            localVideoElement.srcObject = localStream;
            
            // Initialize Daily.co (or use simple WebRTC)
            // For production, integrate with Daily.co API
            // For now, using simple peer-to-peer WebRTC
            
            setupPeerConnection();
            
        } catch (error) {
            console.error('Error accessing media devices:', error);
            alert('Could not access camera/microphone. Please check permissions.');
        }
    }
    
    // Simple WebRTC peer connection setup
    function setupPeerConnection() {
        // This is a simplified version
        // In production, use a signaling server and STUN/TURN servers
        
        const configuration = {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' }
            ]
        };
        
        // For demo purposes, we'll just show local video
        // In production, implement full WebRTC signaling
        console.log('Video call initialized with room:', CONFIG.roomId);
    }
    
    // Toggle video
    toggleVideoBtn?.addEventListener('click', () => {
        isVideoEnabled = !isVideoEnabled;
        localStream.getVideoTracks().forEach(track => {
            track.enabled = isVideoEnabled;
        });
        toggleVideoBtn.textContent = isVideoEnabled ? '📹' : '📹❌';
        toggleVideoBtn.classList.toggle('disabled');
    });
    
    // Toggle audio
    toggleAudioBtn?.addEventListener('click', () => {
        isAudioEnabled = !isAudioEnabled;
        localStream.getAudioTracks().forEach(track => {
            track.enabled = isAudioEnabled;
        });
        toggleAudioBtn.textContent = isAudioEnabled ? '🎤' : '🎤❌';
        toggleAudioBtn.classList.toggle('disabled');
    });
    
    // End call
    endCallBtn?.addEventListener('click', async () => {
        if (confirm('Are you sure you want to end this consultation?')) {
            // Stop all tracks
            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
            }
            
            // Update consultation status
            try {
                await fetch('../api/end-consultation.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        consultation_id: CONFIG.consultationId
                    })
                });
            } catch (error) {
                console.error('Error ending consultation:', error);
            }
            
            window.location.href = '../dashboard.php';
        }
    });
    
    // Chat functionality
    openChatBtn?.addEventListener('click', () => {
        chatSidebar.classList.add('active');
        openChatBtn.style.display = 'none';
    });
    
    toggleChatBtn?.addEventListener('click', () => {
        chatSidebar.classList.remove('active');
        openChatBtn.style.display = 'block';
    });
    
    sendChatBtn?.addEventListener('click', sendMessage);
    chatInput?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
    
    function sendMessage() {
        const message = chatInput.value.trim();
        if (!message) return;
        
        addMessageToChat('You', message, true);
        chatInput.value = '';
        
        // In production, send via WebSocket or signaling server
        // For now, just display locally
    }
    
    function addMessageToChat(sender, message, isLocal = false) {
        const messageEl = document.createElement('div');
        messageEl.className = `chat-message ${isLocal ? 'local' : 'remote'}`;
        messageEl.innerHTML = `
            <div class="message-sender">${sender}</div>
            <div class="message-text">${escapeHtml(message)}</div>
            <div class="message-time">${new Date().toLocaleTimeString()}</div>
        `;
        chatMessages.appendChild(messageEl);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Initialize on page load
    if (CONFIG && CONFIG.roomId) {
        initCall();
    }
})();
