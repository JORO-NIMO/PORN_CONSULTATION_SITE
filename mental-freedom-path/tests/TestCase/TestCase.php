<?php

namespace Tests\TestCase;

use PHPUnit\Framework\TestCase as BaseTestCase;
use PDO;
use PDOException;

class TestCase extends BaseTestCase
{
    protected static $pdo = null;
    protected static $migrationsRun = false;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        
        // Set up test database connection
        $dsn = 'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_TEST_DATABASE');
        $username = getenv('DB_USERNAME');
        $password = getenv('DB_PASSWORD');
        
        try {
            self::$pdo = new PDO($dsn, $username, $password);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Run migrations if not already run
            if (!self::$migrationsRun) {
                self::runMigrations();
                self::$migrationsRun = true;
            }
            
            // Start transaction
            self::$pdo->beginTransaction();
            
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    protected static function runMigrations(): void
    {
        // Run your database migrations here
        // This is a simplified example - you might want to use your actual migration system
        $migrations = [
            "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            // Add other tables as needed
        ];
        
        foreach ($migrations as $migration) {
            self::$pdo->exec($migration);
        }
    }
    
    public static function tearDownAfterClass(): void
    {
        // Rollback transaction
        if (self::$pdo && self::$pdo->inTransaction()) {
            self::$pdo->rollBack();
        }
        
        parent::tearDownAfterClass();
    }
    
    protected function setUp(): void
    {
        parent::setUp();
        // Reset any test data
    }
    
    protected function tearDown(): void
    {
        // Clean up after each test
        parent::tearDown();
    }
}
