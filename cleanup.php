<?php
/**
 * Cleanup Script
 * 
 * This script removes unnecessary files and directories
 * to streamline the project.
 */

// Files to keep
$keepFiles = [
    // Core files
    'index.php',
    'config.php',
    '.htaccess',
    'wellness-chat.html',
    'test-api.php',
    'test-gemini.php',
    
    // API
    'api/chat.php',
    
    // Assets
    'assets/css/style.css',
    'assets/js/wellness-chat.js',
    'assets/js/slideshow.js',
    'assets/images/logo.png',
    'assets/images/favicon.ico',
    
    // App
    'app/services/GeminiWellnessService.php',
    'app/views/ai-assistant/chat.php',
    'app/views/ai-assistant/crisis-resources.php',
    
    // Admin
    'admin/includes/header.php',
    'admin/includes/footer.php',
    'admin/dashboard.php',
    
    // Auth
    'auth/login.php',
    'auth/register.php',
    'auth/logout.php',
    
    // Includes
    'includes/header.php',
    'includes/footer.php',
    'includes/sidebar.php',
    'includes/navbar.php'
];

// Directories to keep
$keepDirs = [
    'api',
    'app',
    'assets',
    'admin',
    'auth',
    'includes'
];

// Files to remove
$filesToRemove = [
    'search.php',
    'scraped-content.php',
    'security_test.php',
    'test.php',
    'test-db.php',
    'test-db-connection.php',
    'test-integration.php',
    'composer',
    'composer.bat',
    'composer.phar',
    'composer.lock',
    'composer.json',
    'phpinfo.php',
    'start-server.bat',
    'student_form.html',
    'commit_message.txt',
    'bg.png'
];

// Directories to remove
$dirsToRemove = [
    'scraper',
    'chat',
    'CascadeProjects',
    'config',
    'data',
    'migrations',
    'vendor',
    'mental-freedom-path'
];

// Function to safely remove files and directories
function removeFile($file) {
    if (file_exists($file)) {
        if (is_dir($file)) {
            array_map('removeFile', glob($file . '/*'));
            @rmdir($file);
            echo "Removed directory: $file\n";
        } else {
            @unlink($file);
            echo "Removed file: $file\n";
        }
    }
}

// Process files to remove
echo "Starting cleanup...\n\n";

foreach ($filesToRemove as $file) {
    $filePath = __DIR__ . '/' . $file;
    if (file_exists($filePath)) {
        removeFile($filePath);
    }
}

// Process directories to remove
foreach ($dirsToRemove as $dir) {
    $dirPath = __DIR__ . '/' . $dir;
    if (is_dir($dirPath)) {
        removeFile($dirPath);
    }
}

echo "\nCleanup complete!\n";
?>
