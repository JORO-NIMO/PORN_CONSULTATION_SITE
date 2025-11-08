<?php
require_once __DIR__ . '/../../config/config.php';

function up($db) {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $sql = "
    ALTER TABLE `contact_messages`
    ADD COLUMN `subject` varchar(255) NULL AFTER `email`,
    ADD COLUMN `phone` varchar(50) NULL AFTER `subject`,
    ADD COLUMN `company` varchar(255) NULL AFTER `phone`;
    ";
    $pdo->exec($sql);
}

function down() {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $sql = "
    ALTER TABLE `contact_messages`
    DROP COLUMN `subject`,
    DROP COLUMN `phone`,
    DROP COLUMN `company`;
    ";
    $pdo->exec($sql);
}
?>