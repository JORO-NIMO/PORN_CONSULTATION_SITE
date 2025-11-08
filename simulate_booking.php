<?php

require_once __DIR__ . '/config/config.php';

// Simulate a logged-in user session
$_SESSION['user_id'] = 1; // Assuming user with ID 1 exists

// Simulate POST request method
$_SERVER['REQUEST_METHOD'] = 'POST';

// Simulate POST data for booking
$_POST['scheduled_time'] = date('Y-m-d H:i:s', strtotime('+1 week')); // Book for one week from now
$_POST['notes'] = 'This is a test booking from the automated script.';

// Simulate GET data for psychiatrist ID
$_GET['psychiatrist'] = 1; // Assuming psychiatrist with ID 1 exists

// Include the book-consultation.php script to process the booking
require_once __DIR__ . '/book-consultation.php';

echo "Booking simulation complete. Please check the database for new entries in 'consultations' and 'messages' tables.\n";

?>