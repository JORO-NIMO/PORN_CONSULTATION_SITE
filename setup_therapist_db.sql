CREATE DATABASE IF NOT EXISTS `therapist_directory`;

USE `therapist_directory`;

CREATE TABLE IF NOT EXISTS `therapists` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `source` VARCHAR(255) DEFAULT 'json',
    `source_id` VARCHAR(255) DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `title` VARCHAR(255) DEFAULT NULL,
    `specialties` TEXT DEFAULT NULL,
    `city` VARCHAR(255) DEFAULT NULL,
    `country` VARCHAR(255) DEFAULT NULL,
    `languages` VARCHAR(255) DEFAULT NULL,
    `contact_email` VARCHAR(255) DEFAULT NULL,
    `phone` VARCHAR(255) DEFAULT NULL,
    `profile_image` VARCHAR(255) DEFAULT NULL,
    `profile_url` VARCHAR(255) DEFAULT NULL,
    `verified` TINYINT(1) DEFAULT 0,
    `last_scraped` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `rating` DECIMAL(2,1) DEFAULT NULL,
    `total_consultations` INT DEFAULT NULL
);