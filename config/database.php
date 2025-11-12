<?php
// Security headers (set only in web contexts, not CLI)
if (PHP_SAPI !== 'cli') {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:; font-src 'self' data: https:; connect-src 'self' ws: wss: https:; frame-ancestors 'none'; base-uri 'self'; form-action 'self';");
}

// Disable error display in production. Use ENVIRONMENT env var to control behavior.
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'development');
}

error_reporting(ENVIRONMENT === 'development' ? E_ALL : 0);
ini_set('display_errors', ENVIRONMENT === 'development' ? '1' : '0');

// Database configuration
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'consultation_site');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static $instance = null;
    private $pdo;
    private $error;
    
    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log and rethrow so callers (or global exception handler) can decide how to respond.
            error_log('Database connection failed: ' . $e->getMessage());
            throw $e;
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

    // Expose the underlying PDO instance for modules needing direct access
    public function getPdo() {
        return $this->pdo;
    }

    // Execute a non-prepared SQL statement (DDL or bulk SQL)
    public function exec($sql) {
        return $this->pdo->exec($sql);
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
        
        $sql = "INSERT INTO `$table` ($columns) VALUES ($placeholders)";
        $this->query($sql, $data);
        
        return $this->lastInsertId();
    }
    
    // Update records in the database
    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach (array_keys($data) as $key) {
            $set[] = "`$key` = :$key";
        }
        
        $sql = "UPDATE `$table` SET " . implode(', ', $set) . " WHERE $where";
        $params = array_merge($data, $whereParams);
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    // Delete records from the database
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM `$table` WHERE $where";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    // Check if a table exists
    public function tableExists($table) {
        try {
            $result = $this->query("SHOW TABLES LIKE '$table'");
            return $result->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // Get a single value from the database
    public function selectValue($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn();
    }
    
    // Get a single row from the database
    public function selectOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    // Get multiple rows from the database
    public function select($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
}

// Create a global instance for backward compatibility. Wrap in try/catch to avoid
// fatal include-time exits; higher-level code can handle a null $db gracefully.
try {
    $db = Database::getInstance();
} catch (Exception $e) {
    error_log('Failed to initialize Database instance: ' . $e->getMessage());
    $db = null;
}
