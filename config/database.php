<?php
// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' https://*.x.com; style-src \'self\' 'unsafe-inline'; img-src \'self\' data: https:; font-src \'self\' data:;');

// Disable error display in production
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development'); // Change to 'production' in production
}

error_reporting(ENVIRONMENT === 'development' ? E_ALL : 0);
ini_set('display_errors', ENVIRONMENT === 'development' ? '1' : '0');

// Database configuration
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $this->pdo = new PDO(
                'sqlite:' . __DIR__ . '/../data/database.sqlite',
                null,
                null,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            if (ENVIRONMENT === 'development') {
                die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
            } else {
                die('Database connection failed. Please try again later.');
            }
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Query failed: ' . $e->getMessage() . ' | Query: ' . $sql);
            throw $e;
        }
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function fetchOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    public function commit() {
        return $this->pdo->commit();
    }
    
    public function rollBack() {
        return $this->pdo->rollBack();
    }
    
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $this->query($sql, $data);
        
        return $this->lastInsertId();
    }
}
// Database file path
define('DB_FILE', __DIR__ . '/../data/database.sqlite');

// Ensure data directory exists
if (!file_exists(dirname(DB_FILE))) {
    mkdir(dirname(DB_FILE), 0755, true);
}

// Set file permissions
if (file_exists(DB_FILE)) {
    chmod(DB_FILE, 0644);
}
            $dataDir = dirname(DB_FILE);
            if (!is_dir($dataDir)) {
                mkdir($dataDir, 0755, true);
            }
            
            // Check if database needs to be created
            $needsSetup = !file_exists(DB_FILE);
            
            $dsn = "sqlite:" . DB_FILE;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->conn = new PDO($dsn, null, null, $options);
            
            // Enable foreign keys for SQLite
            $this->conn->exec('PRAGMA foreign_keys = ON;');
            
            // Initialize database if needed
            if ($needsSetup) {
                $this->setupDatabase();
            }
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function fetchOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
    
    private function setupDatabase() {
        $sql = file_get_contents(__DIR__ . '/setup_sqlite.sql');
        $this->conn->exec($sql);
    }
}
