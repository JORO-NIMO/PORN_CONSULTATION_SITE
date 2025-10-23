<?php
// Simple autoloader for vendor packages present in this repo
$__ca = __DIR__ . '/ca-bundle/ca-bundle-main/src/CaBundle.php';
if (file_exists($__ca)) {
    require_once $__ca;
}
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
