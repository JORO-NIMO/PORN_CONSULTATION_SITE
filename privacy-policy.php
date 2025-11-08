<?php require_once __DIR__ . '/config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .legal-page {
            background: linear-gradient(135deg, #f8fafc 0%, #f5f3ff 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .legal-content {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 10px 40px rgba(99, 102, 241, 0.15);
            animation: slideUp 0.6s ease-out;
        }
        .legal-content h1 {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        .legal-content h2 {
            color: var(--primary);
            margin-top: 2rem;
            margin-bottom: 1rem;
            font-size: 1.75rem;
        }
        .legal-content h3 {
            color: var(--dark);
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }
        .legal-content p, .legal-content li {
            color: var(--text);
            line-height: 1.8;
            margin-bottom: 1rem;
        }
        .legal-content ul {
            margin-left: 2rem;
            margin-bottom: 1rem;
        }
        .last-updated {
            color: var(--text-light);
            font-style: italic;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="legal-page">
        <div class="container">
            <div class="legal-content">
                <h1>Privacy Policy</h1>
                <p class="last-updated">Last Updated: <?php echo date('F d, Y'); ?></p>
                
                <p>At <?php echo SITE_NAME; ?>, we are committed to protecting your privacy and ensuring the confidentiality of your personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our platform.</p>
                
                <h2>1. Information We Collect</h2>
                
                <h3>1.1 Personal Information</h3>
                <p>We collect information that you provide directly to us, including:</p>
                <ul>
                    <li>Name and email address (for account creation)</li>
                    <li>Profile information (optional)</li>
                    <li>Messages and communications with psychiatrists</li>
                    <li>Form submissions and assessment data</li>
                    <li>Consultation booking information</li>
                </ul>
                
                <h3>1.2 Anonymous Information</h3>
                <p>We support anonymous messaging and consultations. When you choose to remain anonymous:</p>
                <ul>
                    <li>Your identity is protected from the recipient</li>
                    <li>We maintain internal records for security purposes only</li>
                    <li>Your anonymity is preserved in all communications</li>
                </ul>
                
                <h3>1.3 Technical Information</h3>
                <p>We automatically collect certain information when you use our platform:</p>
                <ul>
                    <li>IP address and device information</li>
                    <li>Browser type and version</li>
                    <li>Usage data and analytics</li>
                    <li>Session information</li>
                </ul>
                
                <h2>2. How We Use Your Information</h2>
                <p>We use the information we collect to:</p>
                <ul>
                    <li>Provide and maintain our services</li>
                    <li>Facilitate communication with psychiatrists</li>
                    <li>Process consultation bookings</li>
                    <li>Improve our platform and user experience</li>
                    <li>Send important notifications and updates</li>
                    <li>Ensure platform security and prevent fraud</li>
                    <li>Comply with legal obligations</li>
                </ul>
                
                <h2>3. Information Sharing and Disclosure</h2>
                
                <h3>3.1 With Healthcare Professionals</h3>
                <p>We share your information with psychiatrists only when:</p>
                <ul>
                    <li>You book a consultation with them</li>
                    <li>You send them a message (anonymous or identified)</li>
                    <li>It's necessary for providing healthcare services</li>
                </ul>
                
                <h3>3.2 Legal Requirements</h3>
                <p>We may disclose your information if required by law or in response to:</p>
                <ul>
                    <li>Valid legal processes (court orders, subpoenas)</li>
                    <li>Requests from law enforcement</li>
                    <li>Protection of our rights and safety</li>
                    <li>Prevention of harm or illegal activity</li>
                </ul>
                
                <h3>3.3 Third-Party Service Providers</h3>
                <p>We may share information with trusted service providers who assist us in:</p>
                <ul>
                    <li>Hosting and maintaining our platform</li>
                    <li>Processing payments (if applicable)</li>
                    <li>Analyzing usage data</li>
                    <li>Providing customer support</li>
                </ul>
                
                <h2>4. Data Security</h2>
                <p>We implement robust security measures to protect your information:</p>
                <ul>
                    <li>Encrypted data transmission (SSL/TLS)</li>
                    <li>Secure password hashing</li>
                    <li>Regular security audits</li>
                    <li>Access controls and authentication</li>
                    <li>Secure video conferencing for consultations</li>
                </ul>
                
                <h2>5. Your Rights and Choices</h2>
                <p>You have the right to:</p>
                <ul>
                    <li><strong>Access:</strong> Request a copy of your personal data</li>
                    <li><strong>Correction:</strong> Update or correct your information</li>
                    <li><strong>Deletion:</strong> Request deletion of your account and data</li>
                    <li><strong>Anonymity:</strong> Choose to communicate anonymously</li>
                    <li><strong>Opt-out:</strong> Unsubscribe from non-essential communications</li>
                </ul>
                
                <h2>6. Data Retention</h2>
                <p>We retain your information for as long as:</p>
                <ul>
                    <li>Your account is active</li>
                    <li>Necessary to provide our services</li>
                    <li>Required by law or for legitimate business purposes</li>
                    <li>You request deletion (subject to legal requirements)</li>
                </ul>
                
                <h2>7. Children's Privacy</h2>
                <p>Our platform is not intended for users under 13 years of age. We do not knowingly collect personal information from children. If you believe we have collected information from a child, please contact us immediately.</p>
                
                <h2>8. International Data Transfers</h2>
                <p>Your information may be transferred to and processed in countries other than your own. We ensure appropriate safeguards are in place to protect your data in accordance with this Privacy Policy.</p>
                
                <h2>9. Changes to This Privacy Policy</h2>
                <p>We may update this Privacy Policy from time to time. We will notify you of any material changes by:</p>
                <ul>
                    <li>Posting the updated policy on our platform</li>
                    <li>Sending you an email notification</li>
                    <li>Displaying a prominent notice on our website</li>
                </ul>
                
                <h2>10. Contact Us</h2>
                <p>If you have questions or concerns about this Privacy Policy or our data practices, please contact us:</p>
                <ul>
                    <li><strong>Email:</strong> <a href="mailto:joronimoamanya@gmail.com" style="color: var(--primary);">joronimoamanya@gmail.com</a></li>
                    <li><strong>WhatsApp:</strong> <a href="https://wa.me/256726128513" target="_blank" style="color: var(--primary);">+256 726 128513</a></li>
                </ul>
                
                <div style="margin-top: 3rem; padding-top: 2rem; border-top: 2px solid var(--border);">
                    <p style="text-align: center; color: var(--text-light);">
                        By using <?php echo SITE_NAME; ?>, you acknowledge that you have read and understood this Privacy Policy.
                    </p>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
