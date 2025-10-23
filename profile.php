<?php
require_once __DIR__ . '/config/config.php';
requireLogin();
$db = Database::getInstance();
header('Location: /dashboard.php');
exit;
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
        $accountUpdated = false;
        $passwordChanged = false;
        // Handle Account Details (Name/Email)
        $newName = trim($_POST['name'] ?? $user['name']);
        $newEmail = trim($_POST['email'] ?? $user['email']);
        if ($newName === '') {
            $errors[] = 'Name cannot be empty.';
        }
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please provide a valid email address.';
        }
        if (!$errors && ($newName !== $user['name'] || $newEmail !== $user['email'])) {
            $existing = $db->fetchOne('SELECT id FROM users WHERE email = ? AND id != ?', [$newEmail, $user['id']]);
            if ($existing) {
                $errors[] = 'Email is already in use by another account.';
            } else {
                $db->query('UPDATE users SET name = ?, email = ? WHERE id = ?', [$newName, $newEmail, $user['id']]);
                $_SESSION['user_name'] = $newName;
                $_SESSION['user_email'] = $newEmail;
                $accountUpdated = true;
                try { $db->query('INSERT INTO user_activity (user_id, activity_type, details, ip_address, user_agent) VALUES (?,?,?,?,?)', [$user['id'], 'account_update', 'Updated name/email', $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']); } catch (Exception $e) {}
            }
        }
        // Handle Password Change (optional)
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        if ($currentPassword !== '' || $newPassword !== '' || $confirmPassword !== '') {
            if (strlen($newPassword) < 8) {
                $errors[] = 'New password must be at least 8 characters.';
            }
            if ($newPassword !== $confirmPassword) {
                $errors[] = 'New password and confirmation do not match.';
            }
            if (!$errors) {
                $row = $db->fetchOne('SELECT password_hash FROM users WHERE id = ?', [$user['id']]);
                if (!$row || !password_verify($currentPassword, $row['password_hash'])) {
                    $errors[] = 'Current password is incorrect.';
                } else {
                    $newHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
                    $db->query('UPDATE users SET password_hash = ? WHERE id = ?', [$newHash, $user['id']]);
                    $passwordChanged = true;
                    try { $db->query('INSERT INTO user_activity (user_id, activity_type, details, ip_address, user_agent) VALUES (?,?,?,?,?)', [$user['id'], 'password_change', 'Changed account password', $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']); } catch (Exception $e) {}
                }
            }
        }
        // Handle avatar removal (if requested)
        if (!empty($_POST['remove_avatar']) && ($_POST['remove_avatar'] === '1')) {
            if (!empty($profile['avatar'])) {
                $abs = __DIR__ . '/' . $profile['avatar'];
                // Only delete inside uploads directory for safety
                if (strpos(realpath($abs) ?: '', realpath(UPLOAD_DIR)) === 0 && file_exists($abs)) {
                    @unlink($abs);
                }
            }
            $avatarPath = null;
        }

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
            $msgs = ['Profile updated.'];
            if ($accountUpdated) { $msgs[] = 'Account details saved.'; }
            if ($passwordChanged) { $msgs[] = 'Password changed.'; }
            $success = implode(' ', $msgs);
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
                <label>Full Name</label>
                <input type="text" name="name" value="<?php echo sanitize($_POST['name'] ?? $user['name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo sanitize($_POST['email'] ?? $user['email']); ?>" required>
            </div>
            <?php
            $avatarUrl = null;
            if (!empty($profile['avatar'])) {
                $abs = __DIR__ . '/' . $profile['avatar'];
                if (file_exists($abs)) { $avatarUrl = $profile['avatar']; }
            }
            ?>
            <div class="form-group">
                <label>Avatar</label>
                <div style="display:flex; gap:1rem; align-items:center;">
                    <?php if ($avatarUrl): ?>
                        <img src="<?php echo sanitize($avatarUrl); ?>" alt="Avatar" style="width:80px;height:80px;border-radius:50%;object-fit:cover;">
                    <?php else: ?>
                        <div class="avatar-placeholder" style="width:80px;height:80px;border-radius:50%;display:flex;align-items:center;justify-content:center;">👤</div>
                    <?php endif; ?>
                    <input type="file" name="avatar" accept="image/*">
                    <label style="display:flex;align-items:center;gap:.4rem;margin-left:.5rem;">
                        <input type="checkbox" name="remove_avatar" value="1"> Remove
                    </label>
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
            <div class="form-group">
                <label>Current Password (to change password)</label>
                <input type="password" name="current_password" placeholder="Enter current password">
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" placeholder="At least 8 characters">
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" placeholder="Retype new password">
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
