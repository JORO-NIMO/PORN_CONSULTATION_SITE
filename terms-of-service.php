<?php require_once 'config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - <?php echo SITE_NAME; ?></title>
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
                <h1>Terms of Service</h1>
                <p class="last-updated">Last Updated: <?php echo date('F d, Y'); ?></p>
                
                <p>Welcome to <?php echo SITE_NAME; ?>. By accessing or using our platform, you agree to be bound by these Terms of Service. Please read them carefully.</p>
                
                <h2>1. Acceptance of Terms</h2>
                <p>By creating an account or using our services, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service and our Privacy Policy. If you do not agree, please do not use our platform.</p>
                
                <h2>2. Description of Service</h2>
                <p><?php echo SITE_NAME; ?> provides:</p>
                <ul>
                    <li>Access to licensed psychiatrists and mental health professionals</li>
                    <li>Anonymous messaging and consultation services</li>
                    <li>Educational resources about pornography addiction and recovery</li>
                    <li>Assessment forms and progress tracking tools</li>
                    <li>Secure video consultation capabilities</li>
                    <li>Community support features</li>
                </ul>
                
                <h2>3. Eligibility</h2>
                <p>To use our services, you must:</p>
                <ul>
                    <li>Be at least 13 years of age (users under 18 should have parental consent)</li>
                    <li>Provide accurate and complete registration information</li>
                    <li>Maintain the security of your account credentials</li>
                    <li>Not be prohibited from using our services under applicable law</li>
                </ul>
                
                <h2>4. User Accounts</h2>
                
                <h3>4.1 Account Creation</h3>
                <p>When creating an account, you agree to:</p>
                <ul>
                    <li>Provide accurate, current, and complete information</li>
                    <li>Maintain and update your information</li>
                    <li>Keep your password secure and confidential</li>
                    <li>Notify us immediately of any unauthorized access</li>
                </ul>
                
                <h3>4.2 Account Responsibilities</h3>
                <p>You are responsible for:</p>
                <ul>
                    <li>All activities that occur under your account</li>
                    <li>Maintaining the confidentiality of your login credentials</li>
                    <li>Complying with these Terms of Service</li>
                </ul>
                
                <h2>5. Acceptable Use</h2>
                
                <h3>5.1 You Agree NOT To:</h3>
                <ul>
                    <li>Use the platform for any illegal or unauthorized purpose</li>
                    <li>Harass, abuse, or harm other users or psychiatrists</li>
                    <li>Impersonate any person or entity</li>
                    <li>Share explicit or inappropriate content</li>
                    <li>Attempt to gain unauthorized access to our systems</li>
                    <li>Interfere with or disrupt the platform's functionality</li>
                    <li>Use automated systems (bots) without permission</li>
                    <li>Violate any applicable laws or regulations</li>
                </ul>
                
                <h3>5.2 Professional Conduct</h3>
                <p>When communicating with psychiatrists:</p>
                <ul>
                    <li>Be respectful and honest</li>
                    <li>Provide accurate information about your situation</li>
                    <li>Follow professional advice and recommendations</li>
                    <li>Respect appointment times and cancellation policies</li>
                </ul>
                
                <h2>6. Medical Disclaimer</h2>
                <p><strong>IMPORTANT:</strong> <?php echo SITE_NAME; ?> is a platform that connects users with licensed mental health professionals. However:</p>
                <ul>
                    <li>Our platform does NOT provide emergency services</li>
                    <li>In case of emergency, call local emergency services immediately</li>
                    <li>Consultations are not a substitute for in-person medical care when needed</li>
                    <li>We do not guarantee specific treatment outcomes</li>
                    <li>Users are responsible for following professional medical advice</li>
                </ul>
                
                <h2>7. Confidentiality and Privacy</h2>
                
                <h3>7.1 Anonymous Services</h3>
                <p>We offer anonymous messaging and consultations:</p>
                <ul>
                    <li>Your identity can be protected from psychiatrists</li>
                    <li>We maintain internal records for security and legal compliance</li>
                    <li>Anonymity does not apply to illegal activities</li>
                </ul>
                
                <h3>7.2 Professional Confidentiality</h3>
                <p>Psychiatrists are bound by professional confidentiality rules, except when:</p>
                <ul>
                    <li>Required by law to report (e.g., harm to self or others)</li>
                    <li>You provide explicit consent to share information</li>
                    <li>Court orders or legal processes require disclosure</li>
                </ul>
                
                <h2>8. Intellectual Property</h2>
                <p>All content on <?php echo SITE_NAME; ?>, including:</p>
                <ul>
                    <li>Text, graphics, logos, and images</li>
                    <li>Software and platform functionality</li>
                    <li>Educational resources and materials</li>
                    <li>Design and layout</li>
                </ul>
                <p>Are owned by or licensed to <?php echo SITE_NAME; ?> and protected by intellectual property laws. You may not copy, reproduce, or distribute our content without permission.</p>
                
                <h2>9. User-Generated Content</h2>
                <p>When you submit content (messages, forms, assessments):</p>
                <ul>
                    <li>You retain ownership of your content</li>
                    <li>You grant us a license to use it for providing services</li>
                    <li>You represent that you have the right to share the content</li>
                    <li>We may remove content that violates these Terms</li>
                </ul>
                
                <h2>10. Payment and Fees</h2>
                <p>If applicable:</p>
                <ul>
                    <li>Consultation fees will be clearly displayed</li>
                    <li>Payment is required before services are rendered</li>
                    <li>Refund policies will be communicated separately</li>
                    <li>You are responsible for any applicable taxes</li>
                </ul>
                
                <h2>11. Cancellation and Termination</h2>
                
                <h3>11.1 By You</h3>
                <p>You may:</p>
                <ul>
                    <li>Cancel your account at any time</li>
                    <li>Request deletion of your data (subject to legal requirements)</li>
                    <li>Cancel consultations according to our cancellation policy</li>
                </ul>
                
                <h3>11.2 By Us</h3>
                <p>We reserve the right to:</p>
                <ul>
                    <li>Suspend or terminate accounts that violate these Terms</li>
                    <li>Refuse service to anyone for any reason</li>
                    <li>Modify or discontinue services with notice</li>
                </ul>
                
                <h2>12. Disclaimers and Limitations of Liability</h2>
                
                <h3>12.1 Service "As Is"</h3>
                <p>Our platform is provided "as is" without warranties of any kind, including:</p>
                <ul>
                    <li>Uninterrupted or error-free operation</li>
                    <li>Specific treatment outcomes</li>
                    <li>Compatibility with all devices</li>
                </ul>
                
                <h3>12.2 Limitation of Liability</h3>
                <p>To the maximum extent permitted by law, <?php echo SITE_NAME; ?> shall not be liable for:</p>
                <ul>
                    <li>Indirect, incidental, or consequential damages</li>
                    <li>Loss of data or profits</li>
                    <li>Actions or advice of psychiatrists (who are independent professionals)</li>
                    <li>Technical failures or service interruptions</li>
                </ul>
                
                <h2>13. Indemnification</h2>
                <p>You agree to indemnify and hold harmless <?php echo SITE_NAME; ?> from any claims, damages, or expenses arising from:</p>
                <ul>
                    <li>Your use of the platform</li>
                    <li>Your violation of these Terms</li>
                    <li>Your violation of any rights of another party</li>
                </ul>
                
                <h2>14. Changes to Terms</h2>
                <p>We may modify these Terms at any time. We will notify you of material changes by:</p>
                <ul>
                    <li>Posting updated Terms on our platform</li>
                    <li>Sending email notifications</li>
                    <li>Displaying prominent notices</li>
                </ul>
                <p>Continued use after changes constitutes acceptance of the new Terms.</p>
                
                <h2>15. Governing Law</h2>
                <p>These Terms are governed by the laws of Uganda. Any disputes shall be resolved in the courts of Uganda.</p>
                
                <h2>16. Contact Information</h2>
                <p>For questions about these Terms of Service, contact us:</p>
                <ul>
                    <li><strong>Email:</strong> <a href="mailto:joronimoamanya@gmail.com" style="color: var(--primary);">joronimoamanya@gmail.com</a></li>
                    <li><strong>WhatsApp:</strong> <a href="https://wa.me/256726128513" target="_blank" style="color: var(--primary);">+256 726 128513</a></li>
                </ul>
                
                <div style="margin-top: 3rem; padding-top: 2rem; border-top: 2px solid var(--border);">
                    <p style="text-align: center; color: var(--text-light); font-weight: 600;">
                        By using <?php echo SITE_NAME; ?>, you acknowledge that you have read, understood, and agree to these Terms of Service.
                    </p>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
