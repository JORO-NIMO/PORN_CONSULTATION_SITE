<?php
if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/../config/config.php';
}
?>
<aside class="app-sidebar">
    <nav>
        <ul class="side-nav">
            <?php if (isLoggedIn()): ?>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="search.php">Search</a></li>
                <li><a href="psychiatrists.php">Psychiatrists</a></li>
                <li><a href="messages.php">Messages</a></li>
                <li><a href="education.php">Resources</a></li>
                <li><a href="exercises.php">Exercises</a></li>
                <li><a href="discussions.php">Discussions</a></li>
                <li><a href="practitioners.php">Practitioners</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="testimonials.php">Stories</a></li>
                <li><a href="auth/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="index.php">Home</a></li>
                <li><a href="search.php">Search</a></li>
                <li><a href="exercises.php">Exercises</a></li>
                <li><a href="practitioners.php">Practitioners</a></li>
                <li><a href="testimonials.php">Stories</a></li>
                <li><a href="auth/login.php">Login</a></li>
                <li><a href="auth/register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</aside>
