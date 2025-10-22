<?php
// Simple file-based submission handler for consultation requests

// Ensure requests use POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: student_form.html');
    exit;
}

function sanitize(string $value): string {
    return trim($value);
}

$name = sanitize($_POST['name'] ?? '');
$regno = sanitize($_POST['regno'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$course = sanitize($_POST['course'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$lecturer = sanitize($_POST['lecturer'] ?? '');
$preferredTime = sanitize($_POST['preferred_time'] ?? '');
$message = sanitize($_POST['message'] ?? '');

$errors = [];
if ($name === '') { $errors[] = 'Name is required.'; }
if ($regno === '') { $errors[] = 'Registration Number is required.'; }
if ($phone === '' || !preg_match('/^[+]?\d[\d\s-]{6,}$/', $phone)) { $errors[] = 'Valid Phone is required.'; }
if ($course === '') { $errors[] = 'Course is required.'; }
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Valid Email is required.'; }
if ($lecturer === '') { $errors[] = 'Please select a lecturer.'; }
if ($preferredTime === '') { $errors[] = 'Preferred Time is required.'; }
if ($message === '') { $errors[] = 'Message is required.'; }

// Prepare storage
$dataDir = __DIR__ . DIRECTORY_SEPARATOR . 'data';
$dataFile = $dataDir . DIRECTORY_SEPARATOR . 'messages.jsonl'; // JSON Lines

$saved = false;
if (!$errors) {
    if (!is_dir($dataDir)) {
        @mkdir($dataDir, 0775, true);
    }

    $record = [
        'id' => uniqid('msg_', true),
        'submittedAt' => date('c'),
        'name' => $name,
        'regno' => $regno,
        'phone' => $phone,
        'course' => $course,
        'email' => $email,
        'lecturer' => $lecturer,
        'preferredTime' => $preferredTime,
        'message' => $message,
        'reply' => null
    ];

    $json = json_encode($record, JSON_UNESCAPED_UNICODE);
    if ($json !== false) {
        $fh = @fopen($dataFile, 'ab');
        if ($fh) {
            // Exclusive lock for append
            if (flock($fh, LOCK_EX)) {
                fwrite($fh, $json . PHP_EOL);
                fflush($fh);
                flock($fh, LOCK_UN);
                $saved = true;
            }
            fclose($fh);
        }
    }
}

// If AJAX/JSON requested, return JSON and exit
$accept = isset($_SERVER['HTTP_ACCEPT']) ? strtolower($_SERVER['HTTP_ACCEPT']) : '';
$xhr = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) : '';
$wantsJson = (strpos($accept, 'application/json') !== false) || ($xhr === 'xmlhttprequest');
if ($wantsJson) {
    header('Content-Type: application/json; charset=UTF-8');
    if ($errors) {
        echo json_encode([ 'ok' => false, 'errors' => $errors ]);
    } else if ($saved) {
        echo json_encode([ 'ok' => true ]);
    } else {
        echo json_encode([ 'ok' => false, 'errors' => ['An error occurred while saving your request. Please try again later.'] ]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Submission Status</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <main class="container">
    <section class="card">
      <h2>Submission Status</h2>
      <?php if ($errors): ?>
        <div class="alert error">
          <strong>There were problems with your submission:</strong>
          <ul>
            <?php foreach ($errors as $err): ?>
              <li><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="actions">
          <a class="btn" href="student_form.html">Go Back</a>
        </div>
      <?php else: ?>
        <?php if ($saved): ?>
          <div class="alert success">Your consultation request has been submitted successfully.</div>
          <div class="actions">
            <a class="btn" href="index.html">Home</a>
            <a class="btn primary" href="student_form.html">Submit Another</a>
            <a class="btn" href="view_messages.php">View All Requests</a>
          </div>
        <?php else: ?>
          <div class="alert error">An error occurred while saving your request. Please try again later.</div>
          <div class="actions">
            <a class="btn" href="student_form.html">Go Back</a>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>



