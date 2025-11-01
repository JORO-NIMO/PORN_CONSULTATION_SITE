<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_helpers.php';

// Ensure user is admin
requireAdmin();

// Set page title and include header
$pageTitle = 'Content Categories';
require_once 'includes/header.php';

// Handle form submission for adding/editing categories
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_category'])) {
            // Add new category
            $name = trim($_POST['name']);
            $slug = createSlug($name);
            $description = trim($_POST['description'] ?? '');
            
            if (empty($name)) {
                throw new Exception('Category name is required');
            }
            
            // Check if category with same name or slug exists
            $exists = $db->selectOne(
                'SELECT id FROM content_categories WHERE name = ? OR slug = ?', 
                [$name, $slug]
            );
            
            if ($exists) {
                throw new Exception('A category with this name or slug already exists');
            }
            
            $db->insert('content_categories', [
                'name' => $name,
                'slug' => $slug,
                'description' => $description
            ]);
            
            $_SESSION['success'] = 'Category added successfully';
            
        } elseif (isset($_POST['edit_category'])) {
            // Update existing category
            $category_id = (int)$_POST['category_id'];
            $name = trim($_POST['name']);
            $slug = createSlug($name);
            $description = trim($_POST['description'] ?? '');
            
            if (empty($name)) {
                throw new Exception('Category name is required');
            }
            
            // Check if another category with same name or slug exists
            $exists = $db->selectOne(
                'SELECT id FROM content_categories WHERE (name = ? OR slug = ?) AND id != ?', 
                [$name, $slug, $category_id]
            );
            
            if ($exists) {
                throw new Exception('Another category with this name or slug already exists');
            }
            
            $db->update('content_categories', 
                [
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description,
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                ['id' => $category_id]
            );
            
            $_SESSION['success'] = 'Category updated successfully';
            
        } elseif (isset($_POST['delete_category'])) {
            // Delete category
            $category_id = (int)$_POST['category_id'];
            
            // Check if category is in use
            $in_use = $db->selectOne(
                'SELECT id FROM content WHERE category_id = ? LIMIT 1', 
                [$category_id]
            );
            
            if ($in_use) {
                throw new Exception('Cannot delete category: It is being used by one or more content items');
            }
            
            $db->delete('content_categories', ['id' => $category_id]);
            $_SESSION['success'] = 'Category deleted successfully';
        }
        
        // Redirect to prevent form resubmission
        header('Location: categories.php');
        exit();
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        
        // Store form data in session to repopulate form
        if (isset($_POST['add_category']) || isset($_POST['edit_category'])) {
            $_SESSION['form_data'] = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? ''
            ];
            
            if (isset($_POST['edit_category'])) {
                $_SESSION['form_data']['category_id'] = (int)$_POST['category_id'];
            }
        }
        
        // Redirect back to form
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
}

// Get all categories
$categories = $db->select('SELECT * FROM content_categories ORDER BY name');

// Get category to edit if ID is provided
$edit_category = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_category = $db->selectOne(
        'SELECT * FROM content_categories WHERE id = ?', 
        [(int)$_GET['edit']]
    );
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Content Categories</h1>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="row">
                <!-- Add/Edit Category Form -->
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <?= $edit_category ? 'Edit Category' : 'Add New Category' ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="categories.php">
                                <?php if ($edit_category): ?>
                                    <input type="hidden" name="category_id" value="<?= $edit_category['id'] ?>">
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Category Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?= htmlspecialchars($edit_category ? $edit_category['name'] : ($_SESSION['form_data']['name'] ?? '')) ?>" 
                                           required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" 
                                              rows="3"><?= htmlspecialchars($edit_category ? ($edit_category['description'] ?? '') : ($_SESSION['form_data']['description'] ?? '')) ?></textarea>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <?php if ($edit_category): ?>
                                        <button type="submit" name="edit_category" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Update Category
                                        </button>
                                        <a href="categories.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-1"></i> Cancel
                                        </a>
                                    <?php else: ?>
                                        <button type="submit" name="add_category" class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i> Add Category
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </form>
                            
                            <?php unset($_SESSION['form_data']); // Clear form data after display ?>
                        </div>
                    </div>
                </div>
                
                <!-- Categories List -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">All Categories</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($categories)): ?>
                                <div class="alert alert-info mb-0">No categories found. Add your first category using the form.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Slug</th>
                                                <th>Description</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($categories as $category): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($category['name']) ?></td>
                                                    <td><code><?= htmlspecialchars($category['slug']) ?></code></td>
                                                    <td><?= !empty($category['description']) ? htmlspecialchars(substr($category['description'], 0, 50)) . (strlen($category['description']) > 50 ? '...' : '') : '-' ?></td>
                                                    <td>
                                                        <a href="categories.php?edit=<?= $category['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="categories.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category? This action cannot be undone.');">
                                                            <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                                                            <button type="submit" name="delete_category" class="btn btn-sm btn-outline-danger" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php 
// Function to create URL-friendly slug
function createSlug($string) {
    $string = preg_replace('/[^\p{L}0-9\s-]/u', '', $string); // Remove special chars
    $string = str_replace(' ', '-', $string); // Replace spaces with -
    $string = preg_replace('/-+/', '-', $string); // Replace multiple - with single -
    return strtolower($string);
}

require_once 'includes/footer.php'; 
?>
