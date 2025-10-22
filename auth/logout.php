<?php
require_once '../config/config.php';

if (isset($_SESSION['user_id'])) {
    $db = Database::getInstance();
    $db->query("DELETE FROM sessions WHERE user_id = ?", [$_SESSION['user_id']]);
}

session_destroy();
header('Location: login.php');
exit;
