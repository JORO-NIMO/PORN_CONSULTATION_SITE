<?php
// Simple autoloader for TwitterOAuth
require_once __DIR__ . '/composer/ca-bundle/src/CaBundle.php';
spl_autoload_register(function ($class) {
    $prefix = 'Abraham\\TwitterOAuth\\';
    $base_dir = __DIR__ . '/abraham/twitteroauth/src/';
    $len = strlen($prefix);
    
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});
