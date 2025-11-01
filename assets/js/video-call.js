// WebRTC Video Call using Daily.co
(function() {
    let callFrame;
    let localStream;
    let isVideoEnabled = true;
    let isAudioEnabled = true;
    let socket;
    let peerConnection;
    const WS_URL = (location.protocol === 'https:' ? 'wss://' : 'ws://') + location.hostname + ':8080';
    
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
            
            setupPeerConnection();
            connectWebSocket();
            
        } catch (error) {
            console.error('Error accessing media devices:', error);
            alert('Could not access camera/microphone. Please check permissions.');
        }
    }
    
    // Simple WebRTC peer connection setup
    function setupPeerConnection() {
        const configuration = {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' }
            ]
        };
        peerConnection = new RTCPeerConnection(configuration);
        
        // Add local tracks
        localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));
        
        // Remote stream handler
        peerConnection.ontrack = (event) => {
            const [remoteStream] = event.streams;
            remoteVideoElement.srcObject = remoteStream;
        };
        
        // ICE candidate handler
        peerConnection.onicecandidate = (event) => {
            if (event.candidate) {
                sendSignal('ice_candidate', { candidate: event.candidate });
            }
        };
    }

    function connectWebSocket() {
        socket = new WebSocket(WS_URL);
        
        socket.onopen = () => {
            // Authenticate shared token
            socket.send(JSON.stringify({
                action: 'auth',
                user_id: CONFIG.userId,
                name: CONFIG.userName,
                token: CONFIG.userToken,
                room_id: CONFIG.roomId
            }));
        };
        
        socket.onmessage = async (event) => {
            const data = JSON.parse(event.data);
            switch (data.type) {
                case 'auth_success':
                    // Join room for signaling and chat
                    socket.send(JSON.stringify({ action: 'join_room', room_id: CONFIG.roomId }));
                    // Request offer from peer in case we joined late
                    socket.send(JSON.stringify({ action: 'webrtc_request_offer' }));
                    break;
                case 'room_joined':
                    // Create and send offer proactively
                    await createAndSendOffer();
                    break;
                case 'message':
                    addMessageToChat(data.user_name || data.name || 'Peer', data.message, false);
                    break;
                case 'webrtc_offer':
                    if (data.payload?.sdp) {
                        await peerConnection.setRemoteDescription(new RTCSessionDescription(data.payload.sdp));
                        const answer = await peerConnection.createAnswer();
                        await peerConnection.setLocalDescription(answer);
                        sendSignal('webrtc_answer', { sdp: answer });
                    }
                    break;
                case 'webrtc_answer':
                    if (data.payload?.sdp) {
                        await peerConnection.setRemoteDescription(new RTCSessionDescription(data.payload.sdp));
                    }
                    break;
                case 'ice_candidate':
                    if (data.payload?.candidate) {
                        try {
                            await peerConnection.addIceCandidate(new RTCIceCandidate(data.payload.candidate));
                        } catch (err) {
                            console.error('Error adding ICE candidate', err);
                        }
                    }
                    break;
                default:
                    // ignore unknown
                    break;
            }
        };
        
        socket.onerror = (err) => {
            console.error('WebSocket error', err);
        };
        socket.onclose = () => {
            console.log('WebSocket disconnected');
        };
    }

    async function createAndSendOffer() {
        try {
            const offer = await peerConnection.createOffer();
            await peerConnection.setLocalDescription(offer);
            sendSignal('webrtc_offer', { sdp: offer });
        } catch (e) {
            console.error('Failed to create/send offer', e);
        }
    }

    function sendSignal(action, payload) {
        if (!socket || socket.readyState !== WebSocket.OPEN) return;
        socket.send(JSON.stringify({ action, payload }));
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
        
        // Send via WebSocket to peers in room
        if (socket && socket.readyState === WebSocket.OPEN) {
            socket.send(JSON.stringify({ action: 'message', message }));
        }
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
