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
        
    </div>
</header>

<div class="app-layout">
    <?php // include __DIR__ . '/sidebar.php'; ?>
    <div class="app-main">

