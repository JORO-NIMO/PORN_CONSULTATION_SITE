<?php
// PHPMailer helper for sending therapist claim verification emails

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Try to load Composer autoload
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

// Load existing mail settings from Mental Freedom Path config when available
$mfpConfig = __DIR__ . '/../mental-freedom-path/config/config.php';
if (file_exists($mfpConfig)) {
    require_once $mfpConfig;
}

/**
 * Send claim verification email using PHPMailer and existing MAIL_* settings.
 *
 * @param string $toEmail Recipient email address
 * @param string $verifyUrl Absolute verification URL
 * @param array $context Optional extra context for the email (therapist name, etc.)
 * @return bool True on success, false on failure
 */
function send_claim_email(string $toEmail, string $verifyUrl, array $context = []): bool {
    $mailer = new PHPMailer(true);

    try {
        // Server settings
        $host = defined('MAIL_HOST') ? MAIL_HOST : (getenv('MAIL_HOST') ?: 'smtp-relay.brevo.com');
        $port = defined('MAIL_PORT') ? MAIL_PORT : (int)(getenv('MAIL_PORT') ?: 587);
        $encryption = defined('MAIL_ENCRYPTION') ? MAIL_ENCRYPTION : (getenv('MAIL_ENCRYPTION') ?: 'tls');
        $username = defined('MAIL_USERNAME') ? MAIL_USERNAME : (getenv('MAIL_USERNAME') ?: '');
        $password = defined('MAIL_PASSWORD') ? MAIL_PASSWORD : (getenv('MAIL_PASSWORD') ?: '');
        $fromEmail = defined('MAIL_FROM') ? MAIL_FROM : ($username ?: 'noreply@example.com');
        $fromName  = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : (defined('APP_NAME') ? APP_NAME : 'Mental Freedom Path');

        $mailer->isSMTP();
        $mailer->Host = $host;
        $mailer->Port = $port;
        $mailer->SMTPAuth = !empty($username);
        $mailer->Username = $username;
        $mailer->Password = $password;
        if ($encryption === 'tls' || $encryption === 'ssl') {
            $mailer->SMTPSecure = $encryption;
        }

        // TLS options to avoid self-signed issues in local dev
        $mailer->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];

        // Recipients
        $mailer->setFrom($fromEmail, $fromName);
        $mailer->addAddress($toEmail);

        // Content
        $mailer->isHTML(true);
        $mailer->Subject = 'Verify your therapist profile claim';

        $therapistName = $context['therapist_name'] ?? '';
        $siteName = $fromName;
        $mailer->Body = (
            '<p>Hello' . ($therapistName ? ' ' . htmlspecialchars($therapistName) : '') . ',</p>' .
            '<p>We received a request to claim your therapist profile on ' . htmlspecialchars($siteName) . '.</p>' .
            '<p>Please verify your claim by clicking the link below:</p>' .
            '<p><a href="' . htmlspecialchars($verifyUrl) . '">' . htmlspecialchars($verifyUrl) . '</a></p>' .
            '<p>This link will expire in 24 hours. If you did not initiate this request, you can ignore this email.</p>' .
            '<p>Thank you,<br>' . htmlspecialchars($siteName) . ' Team</p>'
        );

        $mailer->AltBody = "Verify your therapist profile claim: $verifyUrl";

        return $mailer->send();
    } catch (Exception $e) {
        error_log('PHPMailer error (claim): ' . $e->getMessage());
        return false;
    }
}