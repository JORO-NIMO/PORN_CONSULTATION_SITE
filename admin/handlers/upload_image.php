<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth_helpers.php';

// Only allow authenticated admins to upload files
if (!isAdmin()) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode([
        'uploaded' => false,
        'error' => ['message' => 'Unauthorized access']
    ]);
    exit();
}

// Check if file was uploaded without errors
if (!isset($_FILES['upload']) || $_FILES['upload']['error'] !== UPLOAD_ERR_OK) {
    $error = 'No file was uploaded or there was an upload error.';
    if (isset($_FILES['upload']['error'])) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        $error = $errors[$_FILES['upload']['error']] ?? 'Unknown upload error';
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'uploaded' => false,
        'error' => ['message' => $error]
    ]);
    exit();
}

// File info
$file = $_FILES['upload'];
$file_name = $file['name'];
$file_tmp = $file['tmp_name'];
$file_size = $file['size'];
$file_error = $file['error'];
$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

// Validate file type
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
if (!in_array($file_ext, $allowed_extensions)) {
    header('Content-Type: application/json');
    echo json_encode([
        'uploaded' => false,
        'error' => ['message' => 'Only JPG, JPEG, PNG & GIF files are allowed.']
    ]);
    exit();
}

// Validate file size (max 5MB)
$max_size = 5 * 1024 * 1024; // 5MB
if ($file_size > $max_size) {
    header('Content-Type: application/json');
    echo json_encode([
        'uploaded' => false,
        'error' => ['message' => 'File is too large. Maximum size is 5MB.']
    ]);
    exit();
}

// Create upload directory if it doesn't exist
$upload_dir = __DIR__ . '/../../uploads/editor/' . date('Y/m/');
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generate unique filename
$new_filename = uniqid() . '_' . preg_replace('/[^\w\d\._\-]/', '_', $file_name);
$destination = $upload_dir . $new_filename;
$relative_path = 'uploads/editor/' . date('Y/m/') . $new_filename;

// Move the file to the upload directory
if (move_uploaded_file($file_tmp, $destination)) {
    // Save to media library
    try {
        $db->insert('media', [
            'user_id' => $_SESSION['user_id'],
            'filename' => $new_filename,
            'original_name' => $file_name,
            'mime_type' => mime_content_type($destination),
            'size' => $file_size,
            'path' => $relative_path,
            'alt_text' => pathinfo($file_name, PATHINFO_FILENAME),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Return success response
        header('Content-Type: application/json');
        echo json_encode([
            'uploaded' => true,
            'url' => '/' . $relative_path,
            'file' => $new_filename
        ]);
    } catch (Exception $e) {
        // Log the error but still return success since the file was uploaded
        error_log('Error saving to media library: ' . $e->getMessage());
        
        header('Content-Type: application/json');
        echo json_encode([
            'uploaded' => true,
            'url' => '/' . $relative_path,
            'file' => $new_filename
        ]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'uploaded' => false,
        'error' => ['message' => 'Failed to move uploaded file.']
    ]);
}
