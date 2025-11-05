-- Therapist Directory Schema
-- Creates database `therapist_directory` and tables for therapists and claims

CREATE DATABASE IF NOT EXISTS `therapist_directory` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `therapist_directory`;

-- Main therapists table per requirements
CREATE TABLE IF NOT EXISTS `therapists` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `source` VARCHAR(255),
  `source_id` VARCHAR(255) UNIQUE,
  `name` VARCHAR(255),
  `title` VARCHAR(128),
  `specialties` TEXT,
  `city` VARCHAR(128),
  `country` VARCHAR(64),
  `languages` VARCHAR(255),
  `contact_email` VARCHAR(255),
  `phone` VARCHAR(64),
  `profile_url` TEXT,
  `profile_html` MEDIUMTEXT,
  `raw_json` JSON,
  `verified` TINYINT(1) DEFAULT 0,
  `last_scraped` DATETIME,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_location` (`city`, `country`),
  INDEX `idx_name` (`name`),
  INDEX `idx_verified` (`verified`)
) ENGINE=InnoDB;

-- Claims table to support email-based verification flow
CREATE TABLE IF NOT EXISTS `therapist_claims` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `therapist_id` INT NOT NULL,
  `claim_email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used_at` DATETIME NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_claim_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `therapists`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uniq_token` (`token`)
) ENGINE=InnoDB;

-- Simple log table for scraper runs
CREATE TABLE IF NOT EXISTS `scrape_runs` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `source` VARCHAR(255) NOT NULL,
  `started_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `completed_at` DATETIME NULL,
  `new_count` INT DEFAULT 0,
  `updated_count` INT DEFAULT 0,
  `failed_count` INT DEFAULT 0,
  `notes` TEXT
) ENGINE=InnoDB;