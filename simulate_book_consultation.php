<?php
// simulate_book_consultation.php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Simulate a logged-in user session
$_SESSION['user_id'] = 1; // Assuming user with ID 1 exists
$_SESSION['username'] = 'testuser';
$_SESSION['role'] = 'user';

// Simulate GET data for psychiatrist selection
$_GET['psychiatrist'] = 1; // Assuming psychiatrist with ID 1 exists

// Simulate POST data for booking submission
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['scheduled_time'] = date('Y-m-d H:i:s', strtotime('+1 week')); // Book for one week from now
$_POST['notes'] = 'This is a simulated consultation booking.';

// Include book-consultation.php to process the simulated submission
require_once __DIR__ . '/book-consultation.php';

// Optionally, you can add some output here to confirm the simulation ran
// echo "Consultation booking simulation complete.";
?>