<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Verify Your Email</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f7f7f7; }
        .card { max-width:600px; margin:20px auto; background:#fff; border:1px solid #eee; border-radius:10px; padding:24px; }
        .btn { display:inline-block; background:#4f46e5; color:#fff; padding:12px 18px; border-radius:8px; text-decoration:none; }
        .muted { color:#666; font-size:12px; }
    </style>
</head>
<body>
    <div class="card">
        <h2 style="margin-top:0;">Verify Your Email</h2>
        <p>Hi <?php echo isset($name) ? htmlspecialchars($name) : 'there'; ?>,</p>
        <p>Thanks for signing up. Please confirm your email to activate your account.</p>
        <?php if (!empty($verificationUrl)): ?>
            <p style="margin: 20px 0;">
                <a href="<?php echo htmlspecialchars($verificationUrl); ?>" class="btn">Verify Email</a>
            </p>
            <p class="muted">If the button doesn't work, copy and paste this URL into your browser:</p>
            <p class="muted"><?php echo htmlspecialchars($verificationUrl); ?></p>
        <?php else: ?>
            <p>Verification link is missing.</p>
        <?php endif; ?>
        <p class="muted">If you didn't create an account, you can ignore this email.</p>
    </div>
    
</body>
</html>