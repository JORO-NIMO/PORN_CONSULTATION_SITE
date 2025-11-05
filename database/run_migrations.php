<?php
// Load configuration and database connection
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Get database instance
$db = Database::getInstance();

$migrationsDir = __DIR__ . '/migrations';

// List of migration files in order
$migrations = [
    '000_create_migrations_table.php',
    '001_create_users_table.php',
    'add_content_management_tables.php',
    'add_role_to_users_table.php',
    '008_create_contact_messages_table.php',
    '009_create_anonymous_messages_table.php'
];

// Create migrations table if it doesn't exist
$db->query("CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Get the current batch number
$batch = $db->fetchOne("SELECT IFNULL(MAX(batch), 0) + 1 as batch FROM migrations")['batch'];

// Get all migration files from the directory
$migrationFiles = scandir($migrationsDir);
$migrationFiles = array_diff($migrationFiles, ['.', '..']); // Remove . and ..

// Get migrations that have already been run
$runMigrations = $db->fetchAll("SELECT migration FROM migrations");
$runMigrations = array_column($runMigrations, 'migration');

echo "Starting migrations...\n";

foreach ($migrationFiles as $file) {
    $migrationPath = $migrationsDir . '/' . $file;
    if (!file_exists($migrationPath)) {
        echo "Skipping missing migration file: $file\n";
        continue;
    }
    
    if (!in_array($file, $runMigrations)) {
        echo "Running migration: $file\n";
        
        // Run the migration in a closure to prevent scope issues
        $runMigration = function($migrationPath) use ($db, &$file, &$batch) {
            include $migrationPath;
            if (function_exists('up')) {
                up($db);
            }
        };

        try {
            // Start transaction
            $db->query("START TRANSACTION");

            // Run the migration
            $runMigration($migrationPath);

            // Record the migration
            $db->query("INSERT INTO migrations (migration, batch) VALUES (?, ?)", [$file, $batch]);

            // Commit the transaction
            $db->query("COMMIT");
            echo "✓ $file completed successfully\n";
            
        } catch (Exception $e) {
            // Rollback on error
            $db->query("ROLLBACK");
            echo "✗ Error in $migration: " . $e->getMessage() . "\n";
            exit(1);
        }
    } else {
        echo "- $migration already run, skipping\n";
    }
}

echo "\nAll migrations completed successfully!\n";
