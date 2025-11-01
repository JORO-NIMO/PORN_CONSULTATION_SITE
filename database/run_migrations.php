<?php
// Load configuration and database connection
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Get database instance
$db = Database::getInstance();

// List of migration files in order
$migrations = [
    '000_create_migrations_table.php',
    '001_create_users_table.php',
    'add_content_management_tables.php',
    'add_role_to_users_table.php'
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

echo "Starting migrations...\n";

foreach ($migrationFiles as $file) {
    $migrationPath = $migrationsDir . '/' . $file;
    if (!file_exists($migrationPath)) {
        echo "Skipping missing migration file: $file\n";
        continue;
    }
    
    if (!in_array($file, $runMigrations)) {
        echo "Running migration: $file\n";
        
        // Include the migration file
        $migrationData = include $migrationPath;
        
        try {
            // Start transaction
            $db->query("START TRANSACTION");
            
            // Run the migration
            if (is_array($migrationData['up'])) {
                foreach ($migrationData['up'] as $sql) {
                    $db->query($sql);
                }
            } else {
                $db->query($migrationData['up']);
            }
            
            // Record the migration
            $db->query("INSERT INTO migrations (migration, batch) VALUES (?, ?)", [$migration, $batch]);
            
            // Commit the transaction
            $db->query("COMMIT");
            echo "✓ $migration completed successfully\n";
            
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
