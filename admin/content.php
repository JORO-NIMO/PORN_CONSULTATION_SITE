<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_helpers.php';

// Ensure user is admin
requireAdmin();

// Set page title and include header
$pageTitle = 'Content Management';
require_once 'includes/header.php';

// Get all content with categories
$content = $db->select(
    "SELECT c.*, cc.name as category_name, u.username as author 
     FROM content c 
     LEFT JOIN content_categories cc ON c.category_id = cc.id 
     LEFT JOIN users u ON c.created_by = u.id 
     ORDER BY c.created_at DESC"
);

// Get all categories for the dropdown
$categories = $db->select('SELECT * FROM content_categories ORDER BY name');
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Content Management</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContentModal">
                        <i class="fas fa-plus me-1"></i> Add New Content
                    </button>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i>
                    All Content
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="contentTable">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Author</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($content as $item): ?>
                                <tr>
                                    <td>
                                        <a href="edit_content.php?id=<?= $item['id'] ?>">
                                            <?= htmlspecialchars($item['title']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($item['category_name'] ?? 'Uncategorized') ?></td>
                                    <td><?= htmlspecialchars($item['author']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $item['status'] === 'published' ? 'success' : ($item['status'] === 'draft' ? 'warning' : 'secondary') ?>">
                                            <?= ucfirst($item['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($item['created_at'])) ?></td>
                                    <td>
                                        <a href="edit_content.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="handlers/delete_content.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this content?');">
                                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
                                            <input type="hidden" name="content_id" value="<?= $item['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add Content Modal -->
<div class="modal fade" id="addContentModal" tabindex="-1" aria-labelledby="addContentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="handlers/save_content.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="addContentModalLabel">Add New Content</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-select" id="category_id" name="category_id">
                                    <option value="">-- Select Category --</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="excerpt" class="form-label">Excerpt</label>
                        <textarea class="form-control" id="excerpt" name="excerpt" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Content *</label>
                        <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="featured_image" class="form-label">Featured Image</label>
                        <input class="form-control" type="file" id="featured_image" name="featured_image" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Content</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Initialize DataTables -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize DataTable
        $('#contentTable').DataTable({
            order: [[4, 'desc']], // Sort by created_at desc by default
            responsive: true
        });
        
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
                height: 300,
                filebrowserUploadUrl: 'handlers/upload_image.php',
                filebrowserUploadMethod: 'form',
                removeDialogTabs: 'image:advanced;link:advanced'
            });
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>
