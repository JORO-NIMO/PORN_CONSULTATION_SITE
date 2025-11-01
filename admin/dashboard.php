<?php
require_once __DIR__ . '/../includes/auth_helpers.php';
requireAdmin(); // This ensures only admins can access this page

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-dashboard">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users me-2"></i>Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="content.php">
                                <i class="fas fa-file-alt me-2"></i>Content
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-danger" href="../auth/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard Overview</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Print</button>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                            <i class="fas fa-calendar me-1"></i> This week
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Total Users</h5>
                                <h2 class="mb-0"><?php echo number_format($db->selectValue('SELECT COUNT(*) FROM users')); ?></h2>
                                <p class="card-text"><small>+5.2% from last month</small></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Active Users</h5>
                                <h2 class="mb-0"><?php 
                                    $activeUsers = $db->selectValue("SELECT COUNT(*) FROM users WHERE last_login > DATE_SUB(NOW(), INTERVAL 30 DAY)");
                                    echo number_format($activeUsers);
                                ?></h2>
                                <p class="card-text"><small>+12.7% from last month</small></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">New This Month</h5>
                                <h2 class="mb-0"><?php 
                                    $newUsers = $db->selectValue("SELECT COUNT(*) FROM users WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)");
                                    echo number_format($newUsers);
                                ?></h2>
                                <p class="card-text"><small>+8.3% from last month</small></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">Active Sessions</h5>
                                <h2 class="mb-0"><?php 
                                    $activeSessions = $db->selectValue("SELECT COUNT(DISTINCT user_id) FROM user_sessions WHERE last_activity > DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
                                    echo number_format($activeSessions);
                                ?></h2>
                                <p class="card-text"><small>Currently active</small></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="row">
                    <div class="col-md-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Recent Activity</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Activity</th>
                                                <th>Time</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $activities = $db->select(
                                                "SELECT u.name, a.activity, a.created_at, a.status 
                                                FROM user_activities a 
                                                JOIN users u ON a.user_id = u.id 
                                                ORDER BY a.created_at DESC 
                                                LIMIT 10"
                                            );
                                            
                                            foreach ($activities as $activity):
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($activity['name']); ?></td>
                                                <td><?php echo htmlspecialchars($activity['activity']); ?></td>
                                                <td><?php echo time_elapsed_string($activity['created_at']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $activity['status'] === 'success' ? 'success' : 'danger'; ?>">
                                                        <?php echo ucfirst($activity['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Quick Actions</h5>
                            </div>
                            <div class="list-group list-group-flush">
                                <a href="users.php?action=add" class="list-group-item list-group-item-action">
                                    <i class="fas fa-user-plus me-2"></i> Add New User
                                </a>
                                <a href="content.php?action=create" class="list-group-item list-group-item-action">
                                    <i class="fas fa-plus-circle me-2"></i> Create New Content
                                </a>
                                <a href="settings.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-cog me-2"></i> System Settings
                                </a>
                                <a href="backup.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-database me-2"></i> Backup Database
                                </a>
                                <a href="logs.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-clipboard-list me-2"></i> View System Logs
                                </a>
                            </div>
                        </div>

                        <!-- System Status -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">System Status</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Storage</span>
                                        <span>75% Used</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-<?php echo disk_free_space('/') < 1073741824 ? 'danger' : 'success'; ?>" 
                                             role="progressbar" 
                                             style="width: 75%" 
                                             aria-valuenow="75" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted">
                                        <?php 
                                        $free = round(disk_free_space('/') / (1024 * 1024), 2);
                                        $total = round(disk_total_space('/') / (1024 * 1024), 2);
                                        echo "$free MB free of $total MB";
                                        ?>
                                    </small>
                                </div>
                                <div class="mb-2">
                                    <i class="fas fa-server me-2"></i>
                                    <span>PHP Version: <?php echo PHP_VERSION; ?></span>
                                </div>
                                <div class="mb-2">
                                    <i class="fas fa-database me-2"></i>
                                    <span>MySQL Version: <?php echo $db->getServerVersion(); ?></span>
                                </div>
                                <div class="mb-0">
                                    <i class="fas fa-clock me-2"></i>
                                    <span>Server Time: <?php echo date('Y-m-d H:i:s'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<!-- Custom Admin JS -->
<script>
// Update dashboard stats every 60 seconds
setInterval(function() {
    fetch('/admin/api/dashboard-stats.php')
        .then(response => response.json())
        .then(data => {
            // Update stats cards
            document.querySelector('.card.bg-primary .h2').textContent = data.totalUsers.toLocaleString();
            document.querySelector('.card.bg-success .h2').textContent = data.activeUsers.toLocaleString();
            document.querySelector('.card.bg-warning .h2').textContent = data.newUsers.toLocaleString();
            document.querySelector('.card.bg-info .h2').textContent = data.activeSessions.toLocaleString();
        });
}, 60000);

// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});
</script>
