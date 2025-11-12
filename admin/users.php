<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/jwt_middleware.php';

$jwt_payload = require_jwt();
$user_id = $jwt_payload['sub'] ?? null;

if (!$user_id) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized: User ID not found in token.']);
    exit;
}

$db = Database::getInstance();

// Check if the user is an admin
$user = $db->fetchOne("SELECT is_admin FROM users WHERE id = ?", [$user_id]);
if (!$user || !$user['is_admin']) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Forbidden: Admin access required.']);
    exit;
}

$pageTitle = 'User Management';
require_once __DIR__ . '/../includes/header.php';

// Handle user actions
$action = $_GET['action'] ?? '';
$userId = $_GET['id'] ?? 0;
$message = '';
$error = '';

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'edit' && $userId) {
        // Update existing user
        try {
            $data = [
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'role' => $_POST['role'],
                'status' => $_POST['status']
            ];
            
            // Only update password if provided
            if (!empty($_POST['password'])) {
                $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
            
            $db->update('users', $data, ['id' => $userId]);
            $message = 'User updated successfully';
        } catch (Exception $e) {
            $error = 'Error updating user: ' . $e->getMessage();
        }
    } elseif ($action === 'add') {
        // Add new user
        try {
            $data = [
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                'role' => $_POST['role'],
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $db->insert('users', $data);
            $message = 'User added successfully';
            $action = ''; // Reset action to show list
        } catch (Exception $e) {
            $error = 'Error adding user: ' . $e->getMessage();
        }
    } elseif ($action === 'delete' && $userId) {
        // Delete user (soft delete)
        try {
            $db->update('users', ['status' => 'deleted'], ['id' => $userId]);
            $message = 'User deleted successfully';
            $action = ''; // Reset action to show list
        } catch (Exception $e) {
            $error = 'Error deleting user: ' . $e->getMessage();
        }
    }
}

// Get user data if in edit mode
$user = null;
if (($action === 'edit' || $action === 'delete') && $userId) {
    $user = $db->selectOne('SELECT * FROM users WHERE id = ?', [$userId]);
    if (!$user) {
        $error = 'User not found';
        $action = ''; // Reset action if user not found
    }
}
?>

<div class="admin-dashboard">
    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__ . '/includes/admin_sidebar.php'; ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <?php 
                        echo $action === 'add' ? 'Add New User' : 
                             ($action === 'edit' ? 'Edit User' : 'User Management'); 
                        ?>
                    </h1>
                    <?php if (!$action): ?>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="?action=add" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-user-plus me-1"></i> Add User
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <!-- User Form -->
                    <div class="card">
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required 
                                           value="<?php echo $user['name'] ?? ''; ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email address</label>
                                    <input type="email" class="form-control" id="email" name="email" required
                                           value="<?php echo $user['email'] ?? ''; ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <?php echo $action === 'add' ? 'Password' : 'New Password (leave blank to keep current)'; ?>
                                    </label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           <?php echo $action === 'add' ? 'required' : ''; ?>>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="role" class="form-label">Role</label>
                                            <select class="form-select" id="role" name="role" required>
                                                <option value="user" <?php echo ($user['role'] ?? '') === 'user' ? 'selected' : ''; ?>>User</option>
                                                <option value="admin" <?php echo ($user['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status" <?php echo $action === 'add' ? 'disabled' : ''; ?>>
                                                <option value="active" <?php echo ($user['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="inactive" <?php echo ($user['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                <?php if ($action === 'edit'): ?>
                                                <option value="suspended" <?php echo ($user['status'] ?? '') === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <a href="users.php" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <?php echo $action === 'add' ? 'Create User' : 'Update User'; ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Users List -->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle" id="usersTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Last Login</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $users = $db->select(
                                            "SELECT id, name, email, role, status, last_login 
                                            FROM users 
                                            WHERE status != 'deleted' 
                                            ORDER BY created_at DESC"
                                        );
                                        
                                        foreach ($users as $user):
                                            $statusClass = [
                                                'active' => 'success',
                                                'inactive' => 'warning',
                                                'suspended' => 'danger',
                                                'pending' => 'info'
                                            ][$user['status']] ?? 'secondary';
                                        ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'primary' : 'secondary'; ?>">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $statusClass; ?>">
                                                    <?php echo ucfirst($user['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                echo $user['last_login'] 
                                                    ? date('M j, Y g:i A', strtotime($user['last_login'])) 
                                                    : 'Never';
                                                ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="?action=edit&id=<?php echo $user['id']; ?>" 
                                                       class="btn btn-outline-primary" 
                                                       title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger delete-user" 
                                                            data-id="<?php echo $user['id']; ?>"
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this user? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDelete" class="btn btn-danger">Delete User</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<!-- DataTables -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#usersTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true
    });

    // Handle delete button click
    $('.delete-user').on('click', function() {
        const userId = $(this).data('id');
        $('#confirmDelete').attr('href', '?action=delete&id=' + userId);
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    });

    // Enable status dropdown for new users
    $('form').on('submit', function() {
        if ($(this).find('select[name="status"]').prop('disabled')) {
            $(this).find('select[name="status"]').prop('disabled', false);
        }
    });
});
</script>
