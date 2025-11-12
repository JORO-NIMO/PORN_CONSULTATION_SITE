<?php
/**
 * WebSocket Chat Server
 * Run this with: php server.php
 */
// Suppress deprecation warnings from vendor packages on PHP 8.2+
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $users;
    protected $db;
    protected $rooms;
    protected $allowedOrigins;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->users = [];
        $this->db = Database::getInstance();
        $this->rooms = [];
        // Define allowed origins for WebSocket connections (tighten for production)
        $origin = defined('BASE_URL') ? parse_url(BASE_URL, PHP_URL_SCHEME) . '://' . parse_url(BASE_URL, PHP_URL_HOST) : 'http://localhost';
        $this->allowedOrigins = [$origin];
        echo "Chat server started\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        // Origin check
        $originHeader = $conn->httpRequest->getHeader('Origin');
        $origin = is_array($originHeader) && count($originHeader) ? $originHeader[0] : '';
        if (!empty($this->allowedOrigins) && $origin && !in_array($origin, $this->allowedOrigins)) {
            echo "Rejected connection from origin: {$origin}\n";
            $conn->close();
            return;
        }

        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        if (!$data) {
            return;
        }

        $response = [
            'type' => 'error',
            'message' => 'Invalid request'
        ];

        try {
            switch ($data['action']) {
                case 'auth':
                    $this->handleAuth($from, $data);
                    break;
                
                case 'join_room':
                    $this->handleJoinRoom($from, $data);
                    break;
                
                case 'message':
                    $this->handleMessage($from, $data);
                    break;
                
                case 'typing':
                    $this->broadcastTyping($from, $data);
                    break;
                
                case 'read_receipt':
                    $this->handleReadReceipt($from, $data);
                    break;
                
                // WebRTC signaling
                case 'webrtc_offer':
                case 'webrtc_answer':
                case 'ice_candidate':
                    $this->handleWebRTCSignal($from, $data);
                    break;
                case 'webrtc_request_offer':
                    $this->handleWebRTCSignal($from, $data);
                    break;
                
                default:
                    $response = [
                        'type' => 'error',
                        'message' => 'Unknown action'
                    ];
                    $from->send(json_encode($response));
            }
        } catch (\Exception $e) {
            $response = [
                'type' => 'error',
                'message' => $e->getMessage()
            ];
            $from->send(json_encode($response));
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // Remove user from active users
        $userId = array_search($conn, $this->users);
        if ($userId !== false) {
            unset($this->users[$userId]);
            $this->broadcastUserList();
            echo "User {$userId} disconnected\n";
        }
        
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    protected function handleAuth($conn, $data) {
        if (empty($data['user_id']) || empty($data['token'])) {
            throw new \Exception('Authentication failed: Missing credentials');
        }

        $userId = (int)$data['user_id'];
        $userName = $data['name'] ?? 'User ' . $userId;
        $token = $data['token'];
        
        // Validate token against video sessions (shared auth)
        $session = $this->db->fetchOne(
            "SELECT consultation_id, room_id FROM video_sessions WHERE user_token = ? OR psychiatrist_token = ? LIMIT 1",
            [$token, $token]
        );
        if (!$session) {
            throw new \Exception('Authentication failed: Invalid token');
        }
        
        // Attach room info if provided
        $conn->roomId = $data['room_id'] ?? $session['room_id'];
        if (!isset($this->rooms[$conn->roomId])) {
            $this->rooms[$conn->roomId] = new \SplObjectStorage();
        }
        $this->rooms[$conn->roomId]->attach($conn);
        
        $this->users[$userId] = $conn;
        $conn->userId = $userId;
        $conn->userName = $userName;
        
        $response = [
            'type' => 'auth_success',
            'user_id' => $userId,
            'name' => $userName,
            'room_id' => $conn->roomId,
            'message' => 'Authentication successful'
        ];
        
        $conn->send(json_encode($response));
        $this->broadcastUserList();
        $this->sendRecentMessages($conn);
        
        echo "User {$userName} ({$userId}) authenticated\n";
    }

    protected function handleJoinRoom($conn, $data) {
        if (empty($data['room_id'])) {
            throw new \Exception('Join failed: Missing room_id');
        }
        $roomId = $data['room_id'];
        $conn->roomId = $roomId;
        if (!isset($this->rooms[$roomId])) {
            $this->rooms[$roomId] = new \SplObjectStorage();
        }
        $this->rooms[$roomId]->attach($conn);
        $conn->send(json_encode(['type' => 'room_joined', 'room_id' => $roomId]));
    }

    protected function handleMessage($from, $data) {
        if (empty($data['message']) || !isset($from->userId)) {
            throw new \Exception('Invalid message');
        }

        $message = [
            'user_id' => $from->userId,
            'user_name' => $from->userName,
            'message' => $data['message'],
            'timestamp' => time(),
            'type' => 'message'
        ];

        // Save to database
        $this->db->insert('chat_messages', [
            'user_id' => $from->userId,
            'room_id' => $from->roomId ?? 1,
            'message' => $data['message'],
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $message['id'] = $this->db->lastInsertId();
        
        // Broadcast to all clients in the same room
        $this->broadcastToRoom($from->roomId ?? null, json_encode($message));
    }

    protected function broadcastTyping($from, $data) {
        if (!isset($from->userId)) return;
        
        $typingData = [
            'type' => 'typing',
            'user_id' => $from->userId,
            'user_name' => $from->userName,
            'is_typing' => $data['is_typing'] ?? false
        ];
        
        $this->broadcastToRoom($from->roomId ?? null, json_encode($typingData), $from);
    }

    protected function handleReadReceipt($from, $data) {
        if (empty($data['message_id']) || !isset($from->userId)) {
            return;
        }
        
        // Update read status in database
        $this->db->query(
            "UPDATE chat_messages SET is_read = 1 WHERE id = ? AND user_id != ?",
            [$data['message_id'], $from->userId]
        );
        
        $receipt = [
            'type' => 'read_receipt',
            'message_id' => $data['message_id'],
            'user_id' => $from->userId,
            'user_name' => $from->userName,
            'timestamp' => time()
        ];
        
        $this->broadcastToRoom($from->roomId ?? null, json_encode($receipt), $from);
    }

    protected function sendRecentMessages($conn, $limit = 50) {
        // Fetch recent messages for the current room
        if (isset($conn->roomId)) {
            $messages = $this->db->fetchAll(
                "SELECT m.*, u.username as user_name 
                 FROM chat_messages m 
                 JOIN users u ON m.user_id = u.id 
                 WHERE m.room_id = ? 
                 ORDER BY m.created_at DESC 
                 LIMIT ?", 
                [$conn->roomId, $limit]
            );
        } else {
            $messages = $this->db->fetchAll(
                "SELECT m.*, u.username as user_name 
                 FROM chat_messages m 
                 JOIN users u ON m.user_id = u.id 
                 ORDER BY m.created_at DESC 
                 LIMIT ?", 
                [$limit]
            );
        }
        
        $response = [
            'type' => 'message_history',
            'messages' => array_reverse($messages)
        ];
        
        $conn->send(json_encode($response));
    }

    protected function broadcastUserList($roomId = null) {
        $users = [];
        foreach ($this->users as $userId => $client) {
            $users[] = [
                'id' => $userId,
                'name' => $client->userName,
                'online' => true
            ];
        }
        
        $response = [
            'type' => 'user_list',
            'users' => $users,
            'count' => count($users)
        ];
        // Broadcast to a specific room if provided, otherwise to all clients
        if ($roomId) {
            $this->broadcastToRoom($roomId, json_encode($response));
        } else {
            $this->broadcast(json_encode($response));
        }
    }

    protected function broadcast($message, $exclude = null) {
        foreach ($this->clients as $client) {
            if ($exclude !== $client) {
                $client->send($message);
            }
        }
    }

    protected function broadcastToRoom($roomId, $message, $exclude = null) {
        if (!$roomId || !isset($this->rooms[$roomId])) {
            return;
        }
        foreach ($this->rooms[$roomId] as $client) {
            if ($exclude !== $client) {
                $client->send($message);
            }
        }
    }

    protected function handleWebRTCSignal($from, $data) {
        if (!isset($from->roomId)) {
            throw new \Exception('Signaling failed: Not in a room');
        }
        $payload = [
            'type' => $data['action'],
            'from' => $from->userId,
            'payload' => $data['payload'] ?? [],
            'timestamp' => time()
        ];
        $this->broadcastToRoom($from->roomId, json_encode($payload), $from);
    }
}

// Run the server application
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    8080
);

echo "Starting chat server on port 8080...\n";
$server->run();
?>
