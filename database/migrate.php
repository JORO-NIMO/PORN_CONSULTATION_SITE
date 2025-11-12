<?php
/**
 * Database Migration Tool
 * 
 * This script runs database migrations for the consultation site.
 * It should be run from the command line or localhost only.
 */

// Load configuration and database connection
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Only allow this to be run from command line or localhost for security
$isCli = php_sapi_name() === 'cli';
$isLocalhost = isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);

if (!$isCli && !$isLocalhost) {
    die('Migrations can only be run from command line or localhost');
}

// Display errors in CLI mode
if ($isCli) {
    error_reporting(0);
ini_set('display_errors', 0);
}

// Function to get all migration files
function getMigrationFiles($dir) {
    $files = [];
    
    if (!is_dir($dir)) {
        die("Error: Directory not found: $dir\n");
    }
    
    $items = scandir($dir);
    
    foreach ($items as $file) {
        if ($file === '.' || $file === '..' || substr($file, -4) !== '.php') {
            continue;
        }
        $files[] = $file;
    }
    
    // Sort files numerically
    natsort($files);
    return array_values($files);
}

// Function to get the current batch number
function getNextBatch($db) {
    $result = $db->selectOne('SELECT MAX(batch) as max_batch FROM migrations');
    return ($result && $result['max_batch'] !== null) ? (int)$result['max_batch'] + 1 : 1;
}

// Function to get already run migrations
function getRunMigrations($db) {
    try {
        $results = $db->select('SELECT migration FROM migrations ORDER BY migration');
        $migrations = [];
        
        foreach ($results as $row) {
            $migrations[] = $row['migration'];
        }
        
        return $migrations;
    } catch (Exception $e) {
        // If migrations table doesn't exist yet, return empty array
        if (strpos($e->getMessage(), 'Table') !== false && strpos($e->getMessage(), 'doesn\'t exist') !== false) {
            return [];
        }
        throw $e;
    }
}

// Main migration function
function runMigrations($db, $migrationsDir) {
    echo "Starting migrations...\n";
    
    // Ensure migrations table exists
    try {
        if (!$db->tableExists('migrations')) {
            echo "Creating migrations table...\n";
            $db->exec("CREATE TABLE IF NOT EXISTS `migrations` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `migration` VARCHAR(255) NOT NULL,
                `batch` INT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `migrations_migration_unique` (`migration`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
            
            echo "Migrations table created.\n";
        }
    } catch (Exception $e) {
        die("Error creating migrations table: " . $e->getMessage() . "\n");
    }
    
    // Get all migration files
    $files = getMigrationFiles($migrationsDir);
    
    if (empty($files)) {
        echo "No migration files found in $migrationsDir\n";
        return;
    }
    
    $runMigrations = getRunMigrations($db);
    $pendingMigrations = array_diff($files, $runMigrations);
    
    if (empty($pendingMigrations)) {
        echo "No new migrations to run.\n";
        return;
    }
    
    echo "Found " . count($pendingMigrations) . " migration(s) to run.\n\n";
    
    $batch = getNextBatch($db);
    $count = 0;
    
    try {
        foreach ($pendingMigrations as $migration) {
            $migrationName = pathinfo($migration, PATHINFO_FILENAME);
            echo "[{$migrationName}] Running... ";
            
            // Include the migration file
            $migrationFile = $migrationsDir . '/' . $migration;
            
            if (!file_exists($migrationFile)) {
                throw new Exception("Migration file not found: $migrationFile");
            }
            
            $migrationData = require $migrationFile;
            
            if (!isset($migrationData['up'])) {
                throw new Exception("Invalid migration file: $migration - missing 'up' key");
            }
            
            // Run the migration
            $queries = is_array($migrationData['up']) ? $migrationData['up'] : [$migrationData['up']];
            
            foreach ($queries as $query) {
                $db->exec($query);
            }
            
            // Record the migration
            $db->insert('migrations', [
                'migration' => $migration,
                'batch' => $batch
            ]);
            
            echo "DONE\n";
            $count++;
        }
        
        echo "\nMigrations completed successfully. $count migration(s) were run.\n";
        
    } catch (Exception $e) {
        echo "FAILED\n\n";
        echo "Error: " . $e->getMessage() . "\n";
        
        if (strpos($e->getMessage(), 'SQLSTATE') !== false) {
            echo "SQL Error Code: " . $e->getCode() . "\n";
        }
        
        if (isset($migrationName)) {
            echo "Failed migration: $migrationName\n";
        }
        
        if (isset($query)) {
            echo "Failed query: " . substr($query, 0, 200) . (strlen($query) > 200 ? '...' : '') . "\n";
        }
        
        exit(1);
    }
}

// Run migrations
try {
    // Get database instance
    $db = Database::getInstance();
    $migrationsDir = __DIR__ . '/migrations';
    
    // Ensure migrations directory exists
    if (!is_dir($migrationsDir)) {
        if (!mkdir($migrationsDir, 0755, true)) {
            die("Error: Could not create migrations directory: $migrationsDir\n");
        }
        echo "Created migrations directory: $migrationsDir\n";
    }
    
    echo "Using migrations from: $migrationsDir\n";
    
    // Run migrations
    runMigrations($db, $migrationsDir);
    
} catch (Exception $e) {
    die("\nMigration error: " . $e->getMessage() . "\n");
}
