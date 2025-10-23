<?php
/**
 * WebSocket Chat Server
 * Run this with: php server.php
 */

require_once __DIR__ . '/../config/database.php';
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

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->users = [];
        $this->db = Database::getInstance();
        echo "Chat server started\n";
    }

    public function onOpen(ConnectionInterface $conn) {
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
                
                case 'message':
                    $this->handleMessage($from, $data);
                    break;
                
                case 'typing':
                    $this->broadcastTyping($from, $data);
                    break;
                
                case 'read_receipt':
                    $this->handleReadReceipt($from, $data);
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

        // In a real app, validate the token against your auth system
        $userId = $data['user_id'];
        $userName = $data['name'] ?? 'User ' . $userId;
        
        $this->users[$userId] = $conn;
        $conn->userId = $userId;
        $conn->userName = $userName;
        
        $response = [
            'type' => 'auth_success',
            'user_id' => $userId,
            'name' => $userName,
            'message' => 'Authentication successful'
        ];
        
        $conn->send(json_encode($response));
        $this->broadcastUserList();
        $this->sendRecentMessages($conn);
        
        echo "User {$userName} ({$userId}) authenticated\n";
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
            'room_id' => $data['room_id'] ?? 1,
            'message' => $data['message'],
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $message['id'] = $this->db->lastInsertId();
        
        // Broadcast to all connected clients
        $this->broadcast(json_encode($message));
    }

    protected function broadcastTyping($from, $data) {
        if (!isset($from->userId)) return;
        
        $typingData = [
            'type' => 'typing',
            'user_id' => $from->userId,
            'user_name' => $from->userName,
            'is_typing' => $data['is_typing'] ?? false
        ];
        
        $this->broadcast(json_encode($typingData), $from);
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
        
        $this->broadcast(json_encode($receipt), $from);
    }

    protected function sendRecentMessages($conn, $limit = 50) {
        $messages = $this->db->fetchAll(
            "SELECT m.*, u.username as user_name 
             FROM chat_messages m 
             JOIN users u ON m.user_id = u.id 
             ORDER BY m.created_at DESC 
             LIMIT ?", 
            [$limit]
        );
        
        $response = [
            'type' => 'message_history',
            'messages' => array_reverse($messages)
        ];
        
        $conn->send(json_encode($response));
    }

    protected function broadcastUserList() {
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
        
        $this->broadcast(json_encode($response));
    }

    protected function broadcast($message, $exclude = null) {
        foreach ($this->clients as $client) {
            if ($exclude !== $client) {
                $client->send($message);
            }
        }
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
