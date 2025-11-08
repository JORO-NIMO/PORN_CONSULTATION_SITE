<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_helpers.php';



// Check if content ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'Invalid content ID';
    header('Location: content.php');
    exit();
}

$content_id = (int)$_GET['id'];

// Get content details
$content = $db->selectOne(
    "SELECT c.*, u.username as author 
     FROM content c 
     LEFT JOIN users u ON c.created_by = u.id 
     WHERE c.id = ?", 
    [$content_id]
);

if (!$content) {
    $_SESSION['error'] = 'Content not found';
    header('Location: content.php');
    exit();
}

// Get all categories for the dropdown
$categories = $db->select('SELECT * FROM content_categories ORDER BY name');

// Set page title and include header
$pageTitle = 'Edit Content: ' . htmlspecialchars($content['title']);
require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Edit Content</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="content.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i> Back to Content
                    </a>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                </div>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form action="handlers/save_content.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="content_id" value="<?= $content['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?= htmlspecialchars($content['title']) ?>" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Category</label>
                                    <select class="form-select" id="category_id" name="category_id">
                                        <option value="">-- Select Category --</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id'] ?>" <?= $content['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($category['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="draft" <?= $content['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                                        <option value="published" <?= $content['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                                        <option value="archived" <?= $content['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="excerpt" class="form-label">Excerpt</label>
                            <textarea class="form-control" id="excerpt" name="excerpt" rows="3"><?= htmlspecialchars($content['excerpt']) ?></textarea>
                            <div class="form-text">A short excerpt or summary of your content.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Content *</label>
                            <textarea class="form-control" id="content" name="content" rows="10" required><?= htmlspecialchars($content['content']) ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="featured_image" class="form-label">Featured Image</label>
                            <?php if (!empty($content['featured_image'])): ?>
                                <div class="mb-2">
                                    <img src="/<?= htmlspecialchars($content['featured_image']) ?>" alt="Featured Image" class="img-thumbnail" style="max-height: 200px;">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="remove_featured_image" id="remove_featured_image" value="1">
                                        <label class="form-check-label" for="remove_featured_image">
                                            Remove featured image
                                        </label>
                                    </div>
                                </div>
                                <p class="text-muted">Upload a new image to replace the current one.</p>
                            <?php endif; ?>
                            <input class="form-control" type="file" id="featured_image" name="featured_image" accept="image/*">
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="content.php" class="btn btn-outline-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete this content? This action cannot be undone.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <form action="handlers/delete_content.php" method="POST" class="d-inline">
                                <input type="hidden" name="content_id" value="<?= $content['id'] ?>">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash me-1"></i> Delete Content
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Initialize CKEditor -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize CKEditor if available
        if (typeof CKEDITOR !== 'undefined') {
            CKEDITOR.replace('content', {
                toolbar: [
                    { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat'] },
                    { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Blockquote'] },
                    { name: 'links', items: ['Link', 'Unlink'] },
                    { name: 'insert', items: ['Image', 'Table', 'HorizontalRule'] },
                    { name: 'styles', items: ['Format', 'Font', 'FontSize'] },
                    { name: 'colors', items: ['TextColor', 'BGColor'] },
                    { name: 'document', items: ['Source'] }
                ],
                height: 400,
                filebrowserUploadUrl: 'handlers/upload_image.php',
                filebrowserUploadMethod: 'form',
                removeDialogTabs: 'image:advanced;link:advanced'
            });
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>
