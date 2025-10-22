<?php
// Handle lecturer reply to a consultation message

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: view_messages.php');
    exit;
}

function sanitize(string $v): string { return trim($v); }

$id = sanitize($_POST['id'] ?? '');
$reply = sanitize($_POST['reply'] ?? '');

$errors = [];
if ($id === '') { $errors[] = 'Message ID is required.'; }
if ($reply === '') { $errors[] = 'Reply text is required.'; }

$dataDir = __DIR__ . DIRECTORY_SEPARATOR . 'data';
$dataFile = $dataDir . DIRECTORY_SEPARATOR . 'messages.jsonl';

$updated = false;
if (!$errors) {
    if (!is_file($dataFile)) {
        $errors[] = 'Data file not found.';
    } else {
        $lines = file($dataFile, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            $errors[] = 'Failed to read data file.';
        } else {
            $outLines = [];
            foreach ($lines as $line) {
                $obj = json_decode(trim($line), true);
                if (is_array($obj) && isset($obj['id']) && $obj['id'] === $id) {
                    $obj['reply'] = $reply;
                    $obj['repliedAt'] = date('c');
                    $line = json_encode($obj, JSON_UNESCAPED_UNICODE);
                    $updated = true;
                }
                if ($line !== null) $outLines[] = $line;
            }
            if (!$updated) {
                $errors[] = 'Message not found.';
            } else {
                // Write to temp file then replace
                $tmpFile = $dataFile . '.tmp';
                $fh = @fopen($tmpFile, 'wb');
                if ($fh && flock($fh, LOCK_EX)) {
                    foreach ($outLines as $l) {
                        fwrite($fh, $l . PHP_EOL);
                    }
                    fflush($fh);
                    flock($fh, LOCK_UN);
                    fclose($fh);
                    // Replace original atomically when possible
                    @rename($tmpFile, $dataFile);
                } else {
                    if ($fh) { fclose($fh); }
                    $errors[] = 'Failed to write updated data.';
                    @unlink($tmpFile);
                    $updated = false;
                }
            }
        }
    }
}

$accept = isset($_SERVER['HTTP_ACCEPT']) ? strtolower($_SERVER['HTTP_ACCEPT']) : '';
$xhr = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) : '';
$wantsJson = (strpos($accept, 'application/json') !== false) || ($xhr === 'xmlhttprequest');
if ($wantsJson) {
    header('Content-Type: application/json; charset=UTF-8');
    if ($errors) {
        echo json_encode(['ok' => false, 'errors' => $errors]);
    } else if ($updated) {
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'errors' => ['Unknown error']]);
    }
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reply Status</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <main class="container">
    <section class="card">
      <h2>Reply Status</h2>
      <?php if ($errors): ?>
        <div class="alert error">
          <strong>There were problems with your reply:</strong>
          <ul>
            <?php foreach ($errors as $err): ?>
              <li><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="actions">
          <a class="btn" href="view_messages.php">Back</a>
        </div>
      <?php else: ?>
        <?php if ($updated): ?>
          <div class="alert success">Reply saved successfully.</div>
          <div class="actions">
            <a class="btn" href="view_messages.php">Back to Messages</a>
          </div>
        <?php else: ?>
          <div class="alert error">An error occurred while saving your reply. Please try again later.</div>
          <div class="actions">
            <a class="btn" href="view_messages.php">Back</a>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
