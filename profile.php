<?php
require_once __DIR__ . '/config/config.php';
requireLogin();
$db = Database::getInstance();
$user = getCurrentUser();
$errors = [];
$success = '';
$profile = $db->fetchOne('SELECT * FROM user_profiles WHERE user_id = ?', [$user['id']]) ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $bio = trim($_POST['bio'] ?? '');
        $preferences = trim($_POST['preferences'] ?? '');
        $avatarPath = $profile['avatar'] ?? null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $f = $_FILES['avatar'];
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
                $errors[] = 'Avatar must be an image (jpg, png, webp).';
            } elseif ($f['size'] > 3 * 1024 * 1024) {
                $errors[] = 'Avatar is too large.';
            } else {
                $dir = UPLOAD_DIR . 'avatars/';
                if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
                $target = $dir . (int)$user['id'] . '_' . time() . '.' . $ext;
                if (@move_uploaded_file($f['tmp_name'], $target)) {
                    $avatarPath = 'uploads/avatars/' . basename($target);
                } else {
                    $errors[] = 'Failed to save avatar.';
                }
            }
        }
        if (!$errors) {
            if ($profile) {
                $db->query('UPDATE user_profiles SET bio = ?, preferences = ?, avatar = ?, updated_at = datetime("now") WHERE user_id = ?', [$bio, $preferences, $avatarPath, $user['id']]);
            } else {
                $db->query('INSERT INTO user_profiles (user_id, bio, preferences, avatar) VALUES (?, ?, ?, ?)', [$user['id'], $bio, $preferences, $avatarPath]);
            }
            $success = 'Profile updated.';
            try { $db->query('INSERT INTO user_activity (user_id, activity_type, details, ip_address, user_agent) VALUES (?,?,?,?,?)', [$user['id'], 'profile_update', 'Updated profile', $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']); } catch (Exception $e) {}
            $profile = $db->fetchOne('SELECT * FROM user_profiles WHERE user_id = ?', [$user['id']]) ?: [];
        }
    }
}

$recent = $db->fetchAll('SELECT * FROM user_activity WHERE user_id = ? ORDER BY created_at DESC LIMIT 10', [$user['id']]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile — <?php echo SITE_NAME; ?></title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<main class="container">
    <h1>Your Profile</h1>
    <?php if ($success): ?><div class="alert success"><?php echo sanitize($success); ?></div><?php endif; ?>
    <?php if ($errors): ?><div class="alert error"><ul><?php foreach ($errors as $e) echo '<li>'.sanitize($e).'</li>'; ?></ul></div><?php endif; ?>

    <div class="dashboard-section">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?php echo generateCSRFToken(); ?>">
            <div class="form-group">
                <label>Avatar</label>
                <div style="display:flex; gap:1rem; align-items:center;">
                    <img src="<?php echo sanitize($profile['avatar'] ?? 'assets/images/avatar-placeholder.png'); ?>" alt="Avatar" style="width:80px;height:80px;border-radius:50%;object-fit:cover;">
                    <input type="file" name="avatar" accept="image/*">
                </div>
            </div>
            <div class="form-group">
                <label>Bio</label>
                <textarea name="bio" rows="4" placeholder="Tell us about yourself..."><?php echo sanitize($profile['bio'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label>Preferences</label>
                <input type="text" name="preferences" placeholder="e.g. mindful, CBT, daily exercises" value="<?php echo sanitize($profile['preferences'] ?? ''); ?>">
            </div>
            <button class="btn btn-primary" type="submit">Save Profile</button>
        </form>
    </div>

    <h2 style="margin-top:2rem;">Recent Activity</h2>
    <ul style="list-style:none;padding:0;display:grid;gap:.75rem;">
        <?php foreach ($recent as $a): ?>
            <li class="content-card" style="padding:1rem;">
                <strong><?php echo sanitize($a['activity_type']); ?></strong>
                <div style="font-size:.9rem;color:var(--text-light);">
                    <?php echo sanitize($a['details'] ?? ''); ?> — <?php echo formatDate($a['created_at']); ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
