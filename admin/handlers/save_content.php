<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth_helpers.php';

// Ensure user is admin and it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access Denied');
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Validate required fields
    $required = ['title', 'content'];
    $errors = [];
    
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst($field) . ' is required';
        }
    }
    
    if (!empty($errors)) {
        throw new Exception(implode('<br>', $errors));
    }
    
    // Sanitize input
    $title = trim($_POST['title']);
    $slug = createSlug($title);
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $content = $_POST['content'];
    $excerpt = !empty($_POST['excerpt']) ? trim($_POST['excerpt']) : substr(strip_tags($content), 0, 200) . '...';
    $status = in_array($_POST['status'], ['draft', 'published', 'archived']) ? $_POST['status'] : 'draft';
    $user_id = $_SESSION['user_id'];
    $now = date('Y-m-d H:i:s');
    
    // Handle file upload
    $featured_image = null;
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../../uploads/content/' . date('Y/m/');
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_name = uniqid() . '_' . basename($_FILES['featured_image']['name']);
        $target_file = $upload_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check if image file is an actual image
        $check = getimagesize($_FILES['featured_image']['tmp_name']);
        if ($check === false) {
            throw new Exception('File is not an image.');
        }
        
        // Check file size (max 5MB)
        if ($_FILES['featured_image']['size'] > 5000000) {
            throw new Exception('Sorry, your file is too large. Maximum size is 5MB.');
        }
        
        // Allow certain file formats
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowed_types)) {
            throw new Exception('Sorry, only JPG, JPEG, PNG & GIF files are allowed.');
        }
        
        // Upload file
        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file)) {
            $featured_image = 'uploads/content/' . date('Y/m/') . $file_name;
            
            // Save to media table
            $db->insert('media', [
                'user_id' => $user_id,
                'filename' => $file_name,
                'original_name' => $_FILES['featured_image']['name'],
                'mime_type' => $_FILES['featured_image']['type'],
                'size' => $_FILES['featured_image']['size'],
                'path' => $featured_image,
                'alt_text' => $title,
                'created_at' => $now
            ]);
        } else {
            throw new Exception('Sorry, there was an error uploading your file.');
        }
    }
    
    // Prepare content data
    $content_data = [
        'title' => $title,
        'slug' => $slug,
        'content' => $content,
        'excerpt' => $excerpt,
        'status' => $status,
        'created_by' => $user_id,
        'updated_at' => $now
    ];
    
    if ($category_id) {
        $content_data['category_id'] = $category_id;
    }
    
    if ($featured_image) {
        $content_data['featured_image'] = $featured_image;
    }
    
    // Check if this is an update or insert
    if (!empty($_POST['content_id'])) {
        // Update existing content
        $content_id = (int)$_POST['content_id'];
        $db->update('content', $content_data, ['id' => $content_id]);
        $message = 'Content updated successfully';
    } else {
        // Insert new content
        $content_data['created_at'] = $now;
        $db->insert('content', $content_data);
        $message = 'Content created successfully';
    }
    
    $_SESSION['success'] = $message;
    header('Location: ../content.php');
    exit();
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    
    // Store form data in session to repopulate form
    $_SESSION['form_data'] = [
        'title' => $_POST['title'] ?? '',
        'category_id' => $_POST['category_id'] ?? '',
        'excerpt' => $_POST['excerpt'] ?? '',
        'content' => $_POST['content'] ?? '',
        'status' => $_POST['status'] ?? 'draft'
    ];
    
    // Redirect back to form
    if (!empty($_POST['content_id'])) {
        header('Location: ../edit_content.php?id=' . (int)$_POST['content_id']);
    } else {
        header('Location: ../content.php');
    }
    exit();
}

/**
 * Create a URL-friendly slug from a string
 */
function createSlug($string) {
    $string = preg_replace('/[^\p{L}0-9\s-]/u', '', $string); // Remove special chars
    $string = str_replace(' ', '-', $string); // Replace spaces with -
    $string = preg_replace('/-+/', '-', $string); // Replace multiple - with single -
    return strtolower($string);
}
