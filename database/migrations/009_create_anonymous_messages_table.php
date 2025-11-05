<?php
require_once __DIR__ . '/../../config/config.php';

function up($db) {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $sql = "
    CREATE TABLE IF NOT EXISTS `anonymous_messages` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `sender_id` int(11) DEFAULT NULL,
      `recipient_id` int(11) NOT NULL,
      `message` text NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ";
    $pdo->exec($sql);
}

function down() {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $sql = "DROP TABLE IF EXISTS `anonymous_messages`";
    $pdo->exec($sql);
}
?>