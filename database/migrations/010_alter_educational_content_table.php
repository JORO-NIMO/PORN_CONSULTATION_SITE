<?php
// Migration: Alter educational_content to add views, tags, and source_url
function up($db) {
    // Add views column
    try {
        $db->query("ALTER TABLE educational_content ADD COLUMN views INT DEFAULT 0");
    } catch (Exception $e) {
        // Ignore if column already exists
    }

    // Add tags column (JSON)
    try {
        $db->query("ALTER TABLE educational_content ADD COLUMN tags JSON NULL");
    } catch (Exception $e) {
        // Ignore if column already exists or JSON not supported
    }

    // Add source_url column
    try {
        $db->query("ALTER TABLE educational_content ADD COLUMN source_url VARCHAR(500) NULL");
    } catch (Exception $e) {
        // Ignore if column already exists
    }
}