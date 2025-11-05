<?php
require_once __DIR__ . '/config.php';

// Generate CSRF token for the form
$csrf = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Anonymous Messaging</title>
    <link rel="stylesheet" href="/assets/styles.css" />
</head>
<body>
    <main class="container">
        <h1>Anonymous Messaging</h1>
        <form id="anonymous-message-form" method="post" action="/api/anonymous-message.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>" />
            <div>
                <label for="recipient">Recipient</label>
                <input id="recipient" name="recipient" type="text" required />
            </div>
            <div>
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="6" required></textarea>
            </div>
            <button type="submit">Send Anonymously</button>
            <p id="status" role="alert" aria-live="polite"></p>
        </form>
    </main>
    <script>
    const form = document.getElementById('anonymous-message-form');
    const status = document.getElementById('status');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(form);
        const response = await fetch('/api/anonymous-message.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (response.ok) {
            status.textContent = result.success;
            status.classList.add('success');
            status.classList.remove('error');
            form.reset();
        } else {
            status.textContent = result.error;
            status.classList.add('error');
            status.classList.remove('success');
        }
    });
    </script>
</body>
</html>