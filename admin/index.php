<?php
/**
 * Admin Dashboard
 */

// Security check
require_once __DIR__ . '/../config/config.php';
requireLogin();
requireAdmin();

// Set page title
$pageTitle = 'Admin Dashboard';

// Include header
include __DIR__ . '/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Share</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                        <span data-feather="calendar"></span>
                        This week
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Total Users</h5>
                            <h2 class="card-text"><?php echo number_format(getTotalUsers()); ?></h2>
                            <a href="users.php" class="text-white">View details <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Active Today</h5>
                            <h2 class="card-text"><?php echo number_format(getActiveUsersToday()); ?></h2>
                            <a href="reports.php?filter=active_today" class="text-white">View details <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">New Content</h5>
                            <h2 class="card-text"><?php echo number_format(getNewContentCount(7)); ?></h2>
                            <a href="content.php?filter=recent" class="text-white">View details <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">Pending</h5>
                            <h2 class="card-text"><?php echo number_format(getPendingItemsCount()); ?></h2>
                            <a href="moderation.php" class="text-white">Review <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>User</th>
                                    <th>Activity</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (getRecentActivities(10) as $activity): ?>
                                <tr>
                                    <td><?php echo formatDate($activity['created_at']); ?></td>
                                    <td><?php echo htmlspecialchars($activity['username'] ?? 'System'); ?></td>
                                    <td><?php echo htmlspecialchars($activity['activity_type']); ?></td>
                                    <td><?php echo htmlspecialchars($activity['details']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="content.php?action=add" class="btn btn-primary mb-2">
                                    <i class="fas fa-plus"></i> Add New Content
                                </a>
                                <a href="daily-topics.php?action=add" class="btn btn-success mb-2">
                                    <i class="fas fa-calendar-plus"></i> Schedule Daily Topic
                                </a>
                                <a href="news/fetch" class="btn btn-info mb-2">
                                    <i class="fas fa-sync"></i> Fetch Latest News
                                </a>
                                <a href="reports.php" class="btn btn-warning">
                                    <i class="fas fa-chart-bar"></i> View Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">System Status</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Database
                                    <span class="badge bg-success rounded-pill">Online</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Storage
                                    <span class="badge bg-<?php echo (getStorageUsage() > 90) ? 'danger' : 'success'; ?> rounded-pill">
                                        <?php echo getStorageUsage(); ?>% used
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Last Backup
                                    <span class="text-muted"><?php echo getLastBackupTime(); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    PHP Version
                                    <span class="text-muted"><?php echo PHP_VERSION; ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
// Include footer
include __DIR__ . '/includes/footer.php';
?>
