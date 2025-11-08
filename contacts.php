<?php
require_once __DIR__ . '/config/config.php';

// Generate CSRF token for the form
$csrf = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contact Us</title>
    <link rel="stylesheet" href="/assets/styles.css" />
</head>
<body>
    <main class="container">
        <h1>Contact Us</h1>
        <form id="contact-form" method="post" action="/api/contact.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>" />
            <div>
                <label for="name">Name</label>
                <input id="name" name="name" type="text" required />
            </div>
            <div>
                <label for="email">Email</label>
                <input id="email" name="email" type="email" required />
            </div>
            <div>
                <label for="subject">Subject</label>
                <input id="subject" name="subject" type="text" />
            </div>
            <div>
                <label for="phone">Phone Number</label>
                <input id="phone" name="phone" type="tel" />
            </div>
            <div>
                <label for="company">Company Name</label>
                <input id="company" name="company" type="text" />
            </div>
            <div>
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="6" required></textarea>
            </div>
            <button type="submit">Send</button>
            <p id="status" role="alert" aria-live="polite"></p>
        </form>
    </main>
    <script>
    const form = document.getElementById('contact-form');
    const statusEl = document.getElementById('status');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        statusEl.textContent = 'Sending...';
        try {
            const formData = new FormData(form);
            const res = await fetch(form.action, { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                statusEl.textContent = 'Message sent successfully.';
                form.reset();
            } else {
                statusEl.textContent = 'Failed to send: ' + (data.error || 'Unknown error');
            }
        } catch (err) {
            statusEl.textContent = 'Network error. Please try again later.';
        }
    });
    </script>
</body>
</html>