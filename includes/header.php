<header>
    <div class="header-content">
        <div>
            <a href="<?php echo isLoggedIn() ? 'dashboard.php' : 'index.php'; ?>" class="logo">
                <?php 
                $logoRel = 'assets/img/logo.png';
                $logoFs = dirname(__DIR__) . '/assets/img/logo.png';
                $logoIconRel = 'assets/img/logo-icon.png';
                $logoIconFs = dirname(__DIR__) . '/assets/img/logo-icon.png';
                if (file_exists($logoFs)) {
                    echo '<img src="' . $logoRel . '" alt="' . SITE_NAME . '" class="site-logo">';
                } elseif (file_exists($logoIconFs)) {
                    echo '<img src="' . $logoIconRel . '" alt="' . SITE_NAME . '" class="site-logo">';
                } else {
                    echo '🧠';
                }
                ?>
                <span class="site-title"><?php echo SITE_NAME; ?></span>
            </a>
            <div style="font-size: 0.75rem; color: var(--text-light); line-height: 1.2;">
                <?php echo SITE_TAGLINE; ?>
            </div>
        </div>
        <nav class="site-nav" style="display:flex; gap: 0.75rem; align-items:center;">
            <a href="index.php">Home</a>
            <a href="education.php">Education</a>
            <a href="psychiatrists.php">Psychiatrists</a>
            <a href="messages.php">Messages</a>
            <a href="anonymous-messaging.php">Anonymous Message</a>
            <a href="contact.php">Contact</a>
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <a href="admin/dashboard.php">Admin</a>
                <?php else: ?>
                    <a href="dashboard.php">Dashboard</a>
                <?php endif; ?>
                <a href="auth/logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<div class="app-layout">
    <?php // include __DIR__ . '/sidebar.php'; ?>
    <div class="app-main">

