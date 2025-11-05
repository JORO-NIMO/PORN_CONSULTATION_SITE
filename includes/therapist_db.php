<?php
// Minimal PDO helper for therapist_directory database

class TherapistDB {
    private PDO $pdo;

    public function __construct(
        string $host = null,
        string $db = 'therapist_directory',
        string $user = null,
        string $pass = null
    ) {
        // Try to load root config for DB constants
        $rootConfig = __DIR__ . '/../config.php';
        if (file_exists($rootConfig)) {
            require_once $rootConfig;
        }

        $host = $host ?? (defined('DB_HOST') ? DB_HOST : '127.0.0.1');
        $user = $user ?? (defined('DB_USER') ? DB_USER : 'root');
        $pass = $pass ?? (defined('DB_PASS') ? DB_PASS : '');

        $dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    public function pdo(): PDO { return $this->pdo; }
}