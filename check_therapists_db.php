<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/therapist_db.php';

$db = new TherapistDB();
$pdo = $db->pdo();

try {
    $stmt = $pdo->query('SELECT * FROM therapists');
    $therapists = $stmt->fetchAll();

    if (empty($therapists)) {
        echo "No therapists found in the database.\n";
    } else {
        echo "Therapists found in the database:\n";
        foreach ($therapists as $therapist) {
            print_r($therapist);
            echo "\n";
        }
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>