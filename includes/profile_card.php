<?php
require_once __DIR__ . '/../config/config.php';

function renderProfileCard(array $opts = []) {
    $defaults = [
        'name' => 'Guest User',
        'title' => 'Welcome to ' . SITE_NAME,
        'handle' => 'guest',
        'status' => isLoggedIn() ? 'Online' : 'Offline',
        'contactText' => 'Contact Me',
        'avatarUrl' => '/assets/img/avatars/a1.png',
        'showUserInfo' => true,
        'enableTilt' => true,
        'enableMobileTilt' => false,
        'contactUrl' => '/contact.php',
    ];
    $cfg = array_merge($defaults, $opts);

    // If logged in and showUserInfo, override from user profile
    if ($cfg['showUserInfo'] && function_exists('getCurrentUser')) {
        $u = getCurrentUser();
        if ($u) {
            $fullName = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
            $cfg['name'] = $fullName !== '' ? $fullName : ($u['name'] ?? $cfg['name']);
            $cfg['handle'] = $u['username'] ?? $cfg['handle'];
            $cfg['title'] = 'Member of ' . SITE_NAME;
            if (!empty($u['profile_image'])) {
                $cfg['avatarUrl'] = $u['profile_image'];
            }
            if (!empty($u['last_login'])) {
                $cfg['status'] = 'Last login: ' . date('M j, Y', strtotime($u['last_login']));
            }
        }
    }

    // Safe fields
    $name = htmlspecialchars($cfg['name']);
    $title = htmlspecialchars($cfg['title']);
    $handle = htmlspecialchars($cfg['handle']);
    $status = htmlspecialchars($cfg['status']);
    $contactText = htmlspecialchars($cfg['contactText']);
    $avatarUrl = htmlspecialchars($cfg['avatarUrl']);
    $contactUrl = htmlspecialchars($cfg['contactUrl']);
    $enableTilt = $cfg['enableTilt'] ? 'true' : 'false';
    $enableMobileTilt = $cfg['enableMobileTilt'] ? 'true' : 'false';

    echo '<div class="profile-card" data-enable-tilt="' . $enableTilt . '" data-enable-mobile-tilt="' . $enableMobileTilt . '">';
    echo '  <div class="pc-inner">';
    echo '    <div class="pc-avatar"><img src="' . $avatarUrl . '" alt="' . $name . '" loading="lazy"></div>';
    echo '    <div class="pc-info">';
    echo '      <div class="pc-name">' . $name . '</div>';
    echo '      <div class="pc-title">' . $title . '</div>';
    echo '      <div class="pc-handle">@' . $handle . '</div>';
    echo '      <div class="pc-status">' . $status . '</div>';
    echo '    </div>';
    echo '    <div class="pc-actions">';
    echo '      <a href="' . $contactUrl . '" class="btn btn-primary pc-contact">' . $contactText . '</a>';
    echo '    </div>';
    echo '  </div>';
    echo '</div>';
}

?>