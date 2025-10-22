<header>
    <div class="header-content">
        <a href="<?php echo isLoggedIn() ? 'dashboard.php' : 'index.php'; ?>" class="logo">
            🌟 Freedom Path
        </a>
        <nav>
            <ul>
                <?php if (isLoggedIn()): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="psychiatrists.php">Psychiatrists</a></li>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="education.php">Resources</a></li>
                    <li><a href="forms.php">Forms</a></li>
                    <li><a href="testimonials.php">Stories</a></li>
                    <li><a href="auth/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="testimonials.php">Stories</a></li>
                    <li><a href="auth/login.php">Login</a></li>
                    <li><a href="auth/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
