<?php
// Lecturer view to display consultation requests

$dataFile = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'messages.jsonl';

function loadMessages(string $filePath): array {
    if (!is_file($filePath)) {
        return [];
    }
    $messages = [];
    $fh = @fopen($filePath, 'rb');
    if (!$fh) { return []; }
    while (($line = fgets($fh)) !== false) {
        $line = trim($line);
        if ($line === '') { continue; }
        $obj = json_decode($line, true);
        if (is_array($obj)) { $messages[] = $obj; }
    }
    fclose($fh);
    // Sort newest first
    usort($messages, function($a, $b) {
        return strcmp($b['submittedAt'] ?? '', $a['submittedAt'] ?? '');
    });
    return $messages;
}

$allMessages = loadMessages($dataFile);

// Filters
$lecturerFilter = isset($_GET['lecturer']) ? trim($_GET['lecturer']) : '';
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

$filtered = array_filter($allMessages, function($m) use ($lecturerFilter, $query) {
    $ok = true;
    if ($lecturerFilter !== '') {
        $ok = $ok && (isset($m['lecturer']) && $m['lecturer'] === $lecturerFilter);
    }
    if ($query !== '') {
        $hay = strtolower(($m['name'] ?? '') . ' ' . ($m['regno'] ?? '') . ' ' . ($m['email'] ?? '') . ' ' . ($m['message'] ?? ''));
        $ok = $ok && (strpos($hay, strtolower($query)) !== false);
    }
    return $ok;
});

// Build lecturer options from data
$lecturers = array_values(array_unique(array_map(function($m){ return $m['lecturer'] ?? ''; }, $allMessages)));
sort($lecturers);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>View Consultation Requests</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <main class="container">
    <section class="card">
      <h2>Consultation Requests</h2>
      <p class="subtitle">Total: <?php echo count($filtered); ?> shown<?php if ($lecturerFilter || $query) { echo ' (filtered)'; } ?></p>

      <form class="form" method="GET" action="">
        <div class="grid two">
          <div class="form-group">
            <label for="lecturer">Filter by Lecturer</label>
            <select id="lecturer" name="lecturer">
              <option value="">All Lecturers</option>
              <?php foreach ($lecturers as $lec): if ($lec === '') continue; ?>
                <option value="<?php echo htmlspecialchars($lec, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($lec === $lecturerFilter ? 'selected' : ''); ?>>
                  <?php echo htmlspecialchars($lec, ENT_QUOTES, 'UTF-8'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="q">Search (name, reg no, email, message)</label>
            <input type="text" id="q" name="q" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Type to search..." />
          </div>
        </div>
        <div class="actions">
          <button class="btn primary" type="submit">Apply Filters</button>
          <a class="btn" href="view_messages.php">Reset</a>
          <a class="btn" href="index.html">Home</a>
        </div>
      </form>

      <?php if (count($filtered) === 0): ?>
        <p class="subtitle">No messages found.</p>
      <?php else: ?>
        <div style="overflow-x:auto; margin-top:1rem;">
          <table>
            <thead>
              <tr>
                <th>Date</th>
                <th>Student</th>
                <th>Reg No</th>
                <th>Phone</th>
                <th>Course</th>
                <th>Email</th>
                <th>Lecturer</th>
                <th>Preferred Time</th>
                <th>Message</th>
                <th>Reply</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($filtered as $m): ?>
                <tr>
                  <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($m['submittedAt'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars($m['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars($m['regno'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars($m['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars($m['course'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars($m['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars($m['lecturer'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars($m['preferredTime'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo nl2br(htmlspecialchars($m['message'] ?? '', ENT_QUOTES, 'UTF-8')); ?></td>
                  <td>
                    <?php if (!empty($m['reply'])): ?>
                      <div class="alert success" style="margin:0 0 .5rem 0;">Reply on record.</div>
                      <div style="white-space:pre-wrap;"><?php echo nl2br(htmlspecialchars($m['reply'], ENT_QUOTES, 'UTF-8')); ?></div>
                    <?php else: ?>
                      <form class="form" method="POST" action="reply_message.php" style="min-width:260px;">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($m['id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                        <div class="form-group">
                          <label for="reply_<?php echo htmlspecialchars($m['id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">Add Reply</label>
                          <textarea id="reply_<?php echo htmlspecialchars($m['id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" name="reply" rows="3" required></textarea>
                        </div>
                        <div class="actions">
                          <button class="btn primary" type="submit">Send Reply</button>
                        </div>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>


