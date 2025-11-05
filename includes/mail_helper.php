<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Attempt to load Composer autoload if present
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

// Load MFP mail config if available
$mfpConfig = __DIR__ . '/../mental-freedom-path/config/config.php';
if (file_exists($mfpConfig)) {
    require_once $mfpConfig;
}

/**
 * Send an email using PHPMailer when available, otherwise fall back to mail().
 *
 * @param string $to Recipient email
 * @param string $subject Subject line
 * @param string $htmlBody HTML body
 * @param string|null $textBody Optional plain-text body
 * @param string|null $replyTo Optional reply-to email
 * @return bool True if sent, false otherwise
 */
function send_mail_safe(string $to, string $subject, string $htmlBody, ?string $textBody = null, ?string $replyTo = null): bool {
    $fromEmail = defined('MAIL_FROM') ? MAIL_FROM : (defined('MAIL_USERNAME') ? MAIL_USERNAME : (defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'noreply@example.com'));
    $fromName  = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : (defined('APP_NAME') ? APP_NAME : (defined('SITE_NAME') ? SITE_NAME : 'Website'));

    // If PHPMailer classes are available, prefer SMTP
    if (class_exists(PHPMailer::class)) {
        try {
            $mailer = new PHPMailer(true);

            $host = defined('MAIL_HOST') ? MAIL_HOST : (getenv('MAIL_HOST') ?: 'smtp-relay.brevo.com');
            $port = defined('MAIL_PORT') ? MAIL_PORT : (int)(getenv('MAIL_PORT') ?: 587);
            $encryption = defined('MAIL_ENCRYPTION') ? MAIL_ENCRYPTION : (getenv('MAIL_ENCRYPTION') ?: 'tls');
            $username = defined('MAIL_USERNAME') ? MAIL_USERNAME : (getenv('MAIL_USERNAME') ?: '');
            $password = defined('MAIL_PASSWORD') ? MAIL_PASSWORD : (getenv('MAIL_PASSWORD') ?: '');

            $mailer->isSMTP();
            $mailer->Host = $host;
            $mailer->Port = $port;
            $mailer->SMTPAuth = !empty($username);
            $mailer->Username = $username;
            $mailer->Password = $password;
            if ($encryption === 'tls' || $encryption === 'ssl') {
                $mailer->SMTPSecure = $encryption;
            }
            $mailer->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];

            $mailer->setFrom($fromEmail, $fromName);
            if ($replyTo) { $mailer->addReplyTo($replyTo); }
            $mailer->addAddress($to);
            $mailer->isHTML(true);
            $mailer->Subject = $subject;
            $mailer->Body = $htmlBody;
            $mailer->AltBody = $textBody ?: strip_tags($htmlBody);
            return $mailer->send();
        } catch (Exception $e) {
            error_log('PHPMailer send error: ' . $e->getMessage());
        }
    }

    // Fallback to mail()
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $fromName . ' <' . $fromEmail . '>'
    ];
    if ($replyTo) { $headers[] = 'Reply-To: ' . $replyTo; }
    return @mail($to, $subject, $htmlBody, implode("\r\n", $headers));
}