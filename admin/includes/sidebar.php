<?php
/**
 * Admin Sidebar
 */

// Get current page for active state highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>" href="index.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Content Management</span>
                </h6>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $currentPage === 'content.php' ? 'active' : ''; ?>" href="content.php">
                    <i class="fas fa-file-alt me-2"></i>
                    All Content
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $currentPage === 'daily-topics.php' ? 'active' : ''; ?>" href="daily-topics.php">
                    <i class="fas fa-calendar-day me-2"></i>
                    Daily Topics
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $currentPage === 'mental-health.php' ? 'active' : ''; ?>" href="mental-health.php">
                    <i class="fas fa-brain me-2"></i>
                    Mental Health Resources
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $currentPage === 'news.php' || strpos($currentPage, 'news/') === 0 ? 'active' : ''; ?>" href="news.php">
                    <i class="fas fa-newspaper me-2"></i>
                    News Articles
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Community</span>
                </h6>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $currentPage === 'chat.php' ? 'active' : ''; ?>" href="chat.php">
                    <i class="fas fa-comments me-2"></i>
                    Chat Moderation
                    <span class="badge bg-danger rounded-pill ms-2">3 New</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $currentPage === 'users.php' ? 'active' : ''; ?>" href="users.php">
                    <i class="fas fa-users me-2"></i>
                    Users
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $currentPage === 'reports.php' ? 'active' : ''; ?>" href="reports.php">
                    <i class="fas fa-flag me-2"></i>
                    Reports
                    <span class="badge bg-warning rounded-pill ms-2">5</span>
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>System</span>
                </h6>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $currentPage === 'settings.php' ? 'active' : ''; ?>" href="settings.php">
                    <i class="fas fa-cog me-2"></i>
                    Settings
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="/" target="_blank">
                    <i class="fas fa-external-link-alt me-2"></i>
                    View Site
                </a>
            </li>
        </ul>
        
        <div class="position-absolute bottom-0 start-0 p-3 w-100 bg-light border-top">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <img src="/assets/img/logo-icon.png" alt="Logo" width="30" height="30" class="rounded-circle">
                </div>
                <div class="flex-grow-1 ms-3">
                    <div class="fw-bold"><?php echo SITE_NAME; ?></div>
                    <small class="text-muted">v1.0.0</small>
                </div>
            </div>
        </div>
    </div>
</nav>
