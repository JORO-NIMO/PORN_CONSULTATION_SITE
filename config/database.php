<?php
// Database configuration - SQLite (portable, no installation needed)
define('DB_FILE', __DIR__ . '/../data/database.sqlite');

class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            // Ensure data directory exists
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
