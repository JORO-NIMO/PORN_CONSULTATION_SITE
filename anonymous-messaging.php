<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/header.php'; // Include header
// Generate CSRF token for the form
$csrf = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Anonymous Messaging</title>
    <link rel="stylesheet" href="/assets/css/style.css" />
    <style>
        /* Additional styles for the messaging interface */
        .messaging-container {
            background-color: var(--light);
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .message-list-sidebar {
            border-right: 1px solid var(--border);
        }

        .chat-area {
            display: flex;
            flex-direction: column;
        }

        #messages-display {
            flex-grow: 1;
            background-color: #fff;
        }

        .message {
            padding: 0.75rem 1rem;
            border-radius: 1rem;
            margin-bottom: 0.5rem;
            max-width: 75%;
            word-wrap: break-word;
        }

        .message.received {
            background-color: var(--light-purple);
            color: var(--text);
            margin-right: auto;
            border-bottom-left-radius: 0.25rem;
        }

        .message.sent {
            background-color: var(--primary);
            color: #fff;
            margin-left: auto;
            border-bottom-right-radius: 0.25rem;
        }

        .message p {
            margin: 0;
            line-height: 1.4;
        }

        .message .timestamp {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 0.25rem;
            display: block;
            text-align: right;
        }

        .message.received .timestamp {
            color: var(--text-light);
        }

        #anonymous-message-form {
            padding-top: 1rem;
            border-top: 1px solid var(--border);
            background-color: var(--light);
        }

        #anonymous-message-form textarea {
            min-height: 40px;
            max-height: 120px;
        }

        #status.success {
            color: var(--success);
        }

        #status.error {
            color: var(--danger);
        }

        .conversation-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .conversation-item:hover {
            background-color: var(--light-purple);
        }

        .conversation-item.active {
            background-color: var(--primary-light);
            color: #fff;
        }

        .conversation-item.active .conversation-name,
        .conversation-item.active .last-message-snippet {
            color: #fff;
        }

        .conversation-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-light);
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            margin-right: 0.75rem;
            flex-shrink: 0;
        }

        .conversation-info {
            flex-grow: 1;
            min-width: 0;
        }

        .conversation-name {
            font-weight: 600;
            color: var(--dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .last-message-snippet {
            font-size: 0.875rem;
            color: var(--text-light);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>
    <main class="container mx-auto mt-10 p-5 bg-white rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold text-center text-primary mb-6">Anonymous Messaging</h1>
        <div class="messaging-container flex flex-col md:flex-row gap-6">
            <div class="message-list-sidebar md:w-1/3 bg-light-purple p-4 rounded-lg">
                <h2 class="text-xl font-semibold text-dark mb-4">Conversations</h2>
                <ul id="conversation-list" class="space-y-2">
                    <!-- Conversations will be loaded here -->
                    <li class="p-3 bg-white rounded-md shadow-sm cursor-pointer hover:bg-gray-50 transition-colors">
                        <span class="font-medium text-primary">Psychiatrist Name</span>
                        <p class="text-sm text-muted truncate">Last message snippet...</p>
                    </li>
                </ul>
            </div>
            <div class="chat-area flex-1 bg-light p-6 rounded-lg shadow-inner">
                <div id="messages-display" class="h-96 overflow-y-auto mb-4 p-4 border border-border rounded-md bg-white">
                    <!-- Messages will be loaded here -->
                    <div class="message received bg-gray-200 p-3 rounded-lg mb-2 max-w-xs">
                        <p>Hello, how can I help you?</p>
                        <span class="text-xs text-muted text-right block mt-1">10:00 AM</span>
                    </div>
                    <div class="message sent bg-primary-light text-white p-3 rounded-lg mb-2 ml-auto max-w-xs">
                        <p>I need some advice.</p>
                        <span class="text-xs text-white text-right block mt-1">10:05 AM</span>
                    </div>
                </div>
                <form id="anonymous-message-form" method="post" action="/api/anonymous-message.php" novalidate class="flex gap-3">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>" />
                    <input type="hidden" id="recipient_id" name="recipient_id" value="" />
                    <textarea id="message" name="message" rows="1" required placeholder="Type your message..." class="flex-1 p-3 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary resize-none"></textarea>
                    <button type="submit" class="btn bg-primary text-white px-6 py-3 rounded-md font-semibold hover:bg-primary-dark transition-colors">Send</button>
                </form>
                <p id="status" role="alert" aria-live="polite" class="mt-4 text-center text-sm"></p>
            </div>
        </div>
    </main>
    <script>
    const form = document.getElementById('anonymous-message-form');
    const status = document.getElementById('status');
    const messagesDisplay = document.getElementById('messages-display');
    const conversationList = document.getElementById('conversation-list');
    const recipientIdInput = document.getElementById('recipient_id');

    let currentRecipientId = null;

    // Function to load messages for a given recipient
    async function loadMessages(recipientId, recipientUsername) {
        currentRecipientId = recipientId;
        messagesDisplay.innerHTML = '';
        recipientIdInput.value = recipientId;

        // Update active conversation item
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.classList.remove('active');
        });
        const activeItem = document.querySelector(`.conversation-item[data-recipient-id="${recipientId}"]`);
        if (activeItem) {
            activeItem.classList.add('active');
        }

        // Fetch messages from API
        const response = await fetch(`/api/get-messages.php?recipient_id=${recipientId}`);
        const result = await response.json();

        if (response.ok) {
            result.messages.forEach(msg => {
                const messageDiv = document.createElement('div');
                messageDiv.classList.add('message', msg.is_sent_by_user ? 'sent' : 'received');
                messageDiv.innerHTML = `<p>${msg.content}</p><span class="timestamp">${new Date(msg.timestamp).toLocaleTimeString()}</span>`;
                messagesDisplay.appendChild(messageDiv);
            });
            messagesDisplay.scrollTop = messagesDisplay.scrollHeight; // Scroll to bottom
        } else {
            status.textContent = result.error;
            status.classList.add('error');
            status.classList.remove('success');
        }
    }

    // Function to load conversations (now therapists)
    async function loadTherapists() {
        const response = await fetch('/api/therapists'); // Fetch from the therapists API
        const result = await response.json();

        if (response.ok) {
            conversationList.innerHTML = ''; // Clear existing conversations
            result.data.forEach(therapist => {
                const li = document.createElement('li');
                li.classList.add('conversation-item');
                li.setAttribute('data-recipient-id', therapist.id);
                li.innerHTML = `
                    <div class="conversation-avatar">${therapist.name.charAt(0).toUpperCase()}</div>
                    <div class="conversation-info">
                        <div class="conversation-name">${therapist.name}</div>
                        <div class="last-message-snippet">${therapist.specialization}</div>
                    </div>
                `;
                li.addEventListener('click', () => loadMessages(therapist.id, therapist.name));
                conversationList.appendChild(li);
            });

            // Automatically load messages for the first therapist if available
            if (result.data.length > 0 && !currentRecipientId) {
                loadMessages(result.data[0].id, result.data[0].name);
            }
        } else {
            console.error('Failed to load therapists:', result.error);
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!currentRecipientId) {
            status.textContent = 'Please select a therapist to send a message.';
            status.classList.add('error');
            status.classList.remove('success');
            return;
        }

        const formData = new FormData(form);
        formData.set('recipient_id', currentRecipientId); // Ensure recipient_id is set

        const response = await fetch('/api/anonymous-message.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (response.ok) {
            status.textContent = result.success;
            status.classList.add('success');
            status.classList.remove('error');
            form.reset();
            loadMessages(currentRecipientId); // Reload messages after sending
            loadTherapists(); // Reload therapists to update last message (if applicable)
        } else {
            status.textContent = result.error;
            status.classList.add('error');
            status.classList.remove('success');
        }
    });

    // Initial load
    loadTherapists();
    </script>
</body>
</html>
<?php require_once __DIR__ . '/includes/footer.php'; // Include footer ?>