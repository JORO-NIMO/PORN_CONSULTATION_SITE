<?php
// New user welcome email
$fname = $data['first_name'] ?? 'there';
$appName = defined('APP_NAME') ? APP_NAME : 'Mental Freedom Path';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome to <?= htmlspecialchars($appName) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        .header {
            background-color: #4CAF50; /* Green */
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
            line-height: 1.6;
        }
        .content p {
            margin: 0 0 15px;
        }
        .footer {
            background-color: #f4f4f4;
            color: #777;
            padding: 20px;
            text-align: center;
            font-size: 12px;
        }
        .button {
            display: inline-block;
            background-color: #4CAF50;
            color: #ffffff;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to <?= htmlspecialchars($appName) ?>!</h1>
        </div>
        <div class="content">
            <p>Hi <?= htmlspecialchars($fname) ?>,</p>
            <p>Thank you for signing up to MFPug. Your journey to great minds starts here.</p>
            <p>We are excited to have you on board. You can start by exploring our resources or finding a therapist that is right for you.</p>
            <p style="text-align: center; margin-top: 30px;">
                <a href="<?= defined('APP_URL') ? APP_URL : '#' ?>" class="button">Explore Now</a>
            </p>
        </div>
        <div class="footer">
            <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($appName) ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html>