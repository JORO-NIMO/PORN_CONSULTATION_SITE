<?php
require_once __DIR__ . '/config/config.php';
requireLogin();
$db = Database::getInstance();
$user = getCurrentUser();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $category = $_POST['category'] ?? '';
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if (!in_array($category, ['document','audio','video','research'], true)) {
            $errors[] = 'Invalid category.';
        }
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Please select a file to upload.';
        }
        if (!$errors) {
            $file = $_FILES['file'];
            $allowed = [
                'document' => ['pdf','doc','docx','ppt','pptx','txt','rtf'],
                'audio' => ['mp3','wav','m4a','aac','ogg'],
                'video' => ['mp4','mov','webm','avi','mkv'],
                'research' => ['pdf','doc','docx','csv','xlsx']
            ];
            $limits = [
                'document' => MAX_UPLOAD_SIZE_DOCUMENT,
                'audio' => MAX_UPLOAD_SIZE_AUDIO,
                'video' => MAX_UPLOAD_SIZE_VIDEO,
                'research' => MAX_UPLOAD_SIZE_DOCUMENT
            ];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed[$category], true)) {
                $errors[] = 'File type not allowed for ' . $category . '.';
            }
            if ($file['size'] > ($limits[$category] ?? MAX_FILE_SIZE)) {
                $errors[] = 'File is too large.';
            }
            if (!$errors) {
                $userDir = UPLOAD_DIR . (int)$user['id'] . '/';
                if (!is_dir($userDir)) {
                    @mkdir($userDir, 0755, true);
                }
                $safeName = preg_replace('/[^a-zA-Z0-9-_\.]/','_', basename($file['name']));
                $target = $userDir . time() . '_' . $safeName;
                if (@move_uploaded_file($file['tmp_name'], $target)) {
                    $relPath = 'uploads/' . (int)$user['id'] . '/' . basename($target);
                    $db->query(
                        'INSERT INTO user_uploads (user_id, title, description, category, file_path, original_name, mime_type, file_size) VALUES (?,?,?,?,?,?,?,?)',
                        [
                            $user['id'], $title, $description, $category, $relPath,
                            $file['name'], $file['type'] ?? null, (int)$file['size']
                        ]
                    );
                    $success = 'Upload successful.';
                } else {
                    $errors[] = 'Failed to save file.';
                }
            }
        }
    }
}

$myUploads = $db->fetchAll('SELECT * FROM user_uploads WHERE user_id = ? ORDER BY created_at DESC', [$user['id']]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Uploads — <?php echo SITE_NAME; ?></title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<main class="container">
    <h1>Upload your resources</h1>
    <?php if ($success): ?><div class="alert success"><?php echo sanitize($success); ?></div><?php endif; ?>
    <?php if ($errors): ?><div class="alert error"><ul><?php foreach ($errors as $e) echo '<li>'.sanitize($e).'</li>'; ?></ul></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data" style="background:#fff; padding:1.5rem; border-radius:12px; box-shadow: var(--shadow);">
        <input type="hidden" name="csrf" value="<?php echo generateCSRFToken(); ?>">
        <div class="form-group">
            <label>Category <span class="req">*</span></label>
            <select name="category" required>
                <option value="">Select type</option>
                <option value="document">Document</option>
                <option value="audio">Audio</option>
                <option value="video">Video</option>
                <option value="research">Research</option>
            </select>
        </div>
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" placeholder="Optional title"/>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3" placeholder="Optional description"></textarea>
        </div>
        <div class="form-group">
            <label>File <span class="req">*</span></label>
            <input type="file" name="file" required />
        </div>
        <button class="btn btn-primary" type="submit">Upload</button>
    </form>

    <h2 style="margin-top:2rem;">Your uploads</h2>
    <div class="content-grid">
        <?php foreach ($myUploads as $u): ?>
        <div class="content-card">
            <span class="content-type-badge"><?php echo strtoupper(sanitize($u['category'])); ?></span>
            <h3><?php echo sanitize($u['title'] ?: $u['original_name']); ?></h3>
            <p><?php echo nl2br(sanitize($u['description'] ?: '')); ?></p>
            <div class="card-footer">
                <a class="btn btn-sm btn-secondary" href="<?php echo sanitize($u['file_path']); ?>" target="_blank">View</a>
                <span style="font-size:.85rem; color:var(--text-light);"><?php echo date('Y-m-d H:i', strtotime($u['created_at'])); ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
