<?php
namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    protected $fromEmail;
    protected $fromName;

    public function __construct() {
        $this->fromEmail = defined('MAIL_FROM') ? MAIL_FROM : 'noreply@example.com';
        $this->fromName  = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : (defined('APP_NAME') ? APP_NAME : 'App');
    }

    public function send(string $to, string $subject, string $template, array $data = []): bool {
        $html = $this->renderTemplate($template, $data);
        $text = strip_tags($html); // Basic text version

        if (defined('MAIL_HOST')) {
            return $this->sendViaSMTP($to, $subject, $html, $text);
        }

        // Fallback to mail() if no SMTP config
        $headers = 'From: ' . $this->fromName . ' <' . $this->fromEmail . '>';
        return mail($to, $subject, $html, $headers);
    }

    protected function renderTemplate(string $template, array $data = []): string {
        $templatePath = VIEWS_PATH . '/' . ltrim($template, '/') . '.php';
        if (!file_exists($templatePath)) {
            return 'Template not found: ' . htmlspecialchars($template);
        }
        
        ob_start();
        extract($data);
        include $templatePath;
        return ob_get_clean();
    }

    protected function sendViaSMTP(string $to, string $subject, string $html, string $altText = ''): bool {
        $mailer = new PHPMailer(true);

        try {
            $mailer->isSMTP();
            $mailer->Host = MAIL_HOST;
            $mailer->SMTPAuth = true;
            $mailer->Username = MAIL_USERNAME;
            $mailer->Password = MAIL_PASSWORD;
            $mailer->SMTPSecure = defined('MAIL_ENCRYPTION') ? MAIL_ENCRYPTION : PHPMailer::ENCRYPTION_STARTTLS;
            $mailer->Port = defined('MAIL_PORT') ? MAIL_PORT : 587;

            // Less strict TLS for local dev if needed
            if (defined('APP_ENV') && APP_ENV === 'development') {
                $mailer->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ];
            }

            $mailer->setFrom($this->fromEmail, $this->fromName);
            $mailer->addAddress($to);
            $mailer->isHTML(true);
            $mailer->Subject = $subject;
            $mailer->Body = $html;
            $mailer->AltBody = $altText;

            return $mailer->send();
        } catch (Exception $e) {
            error_log('PHPMailer Error: ' . $mailer->ErrorInfo);
            return false;
        }
    }
}