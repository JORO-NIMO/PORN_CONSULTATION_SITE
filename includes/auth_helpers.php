<?php
// Ensure core configuration is loaded so we use the global isAdmin/email-based check
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Role-based helpers should delegate to the global config functions.
// We intentionally do NOT redefine isAdmin/requireAdmin here to avoid conflicts.

function redirectBasedOnRole(): void {
    try {
        if (isAdmin()) {
            header('Location: /admin/dashboard.php');
        } else {
            header('Location: /dashboard.php');
        }
        exit();
    } catch (Throwable $e) {
        error_log('Redirect error: ' . $e->getMessage());
        header('Location: /dashboard.php');
        exit();
    }
}
