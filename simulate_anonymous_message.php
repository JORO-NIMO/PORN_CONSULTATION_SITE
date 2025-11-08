<?php
// simulate_anonymous_message.php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Simulate a logged-in user session
$_SESSION['user_id'] = 1; // Assuming user with ID 1 exists
$_SESSION['username'] = 'testuser';
$_SESSION['role'] = 'user';

// Simulate POST data for sending an anonymous message
$_POST['psychiatrist_id'] = 1; // Assuming psychiatrist with ID 1 exists
$_POST['subject'] = 'Anonymous Inquiry';
$_POST['message'] = 'This is an anonymous message from a user.';
$_POST['is_anonymous'] = 'on'; // Simulate checkbox being checked
$_POST['send_message'] = '1'; // Simulate form submission

// Include messages.php to process the simulated submission
require_once __DIR__ . '/messages.php';

// Optionally, you can add some output here to confirm the simulation ran
// echo "Anonymous message simulation complete.";
?>