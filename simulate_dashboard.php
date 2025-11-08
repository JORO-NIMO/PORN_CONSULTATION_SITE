<?php

session_start(); // Explicitly start the session

require_once __DIR__ . '/config/config.php';

// Simulate a logged-in user session
$_SESSION['user_id'] = 1; // Assuming user with ID 1 exists
$_SESSION['user_name'] = 'Test User'; // Set a dummy user name for display

// Include the dashboard.php script
require_once __DIR__ . '/dashboard.php';

?>