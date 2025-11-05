<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>New Appointment Request</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f7f7f7; }
        .card { max-width:600px; margin:20px auto; background:#fff; border:1px solid #eee; border-radius:10px; padding:24px; }
        .label { font-weight:bold; }
        .muted { color:#666; font-size:12px; }
    </style>
</head>
<body>
    <div class="card">
        <h2 style="margin-top:0;">New Appointment Request</h2>
        <p>Dear <?php echo isset($counselor->first_name) ? htmlspecialchars($counselor->first_name) : 'Counselor'; ?>,</p>
        <p>You have a new appointment request.</p>
        <?php if (!empty($appointment)): ?>
            <p><span class="label">Requested By:</span> <?php echo isset($user) ? htmlspecialchars(($user->first_name ?? '').' '.($user->last_name ?? '')) : 'User'; ?></p>
            <p><span class="label">Scheduled Time:</span> <?php echo htmlspecialchars($appointment->appointment_date ?? ($appointment->scheduled_time ?? 'TBD')); ?></p>
            <p><span class="label">Duration:</span> <?php echo htmlspecialchars($appointment->duration_minutes ?? '60'); ?> minutes</p>
            <?php if (!empty($appointment->notes)): ?>
                <p><span class="label">Notes:</span> <?php echo nl2br(htmlspecialchars($appointment->notes)); ?></p>
            <?php endif; ?>
        <?php endif; ?>
        <p class="muted">Please log in to the dashboard to accept or reschedule.</p>
    </div>
</body>
</html>