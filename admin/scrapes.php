<?php
require_once __DIR__ . '/../includes/auth_helpers.php';
requireAdmin();

$pageTitle = 'Scraper Runs';
require_once __DIR__ . '/../admin/includes/header.php';
require_once __DIR__ . '/../includes/therapist_db.php';

$db = new TherapistDB();
$pdo = $db->pdo();

// Handle re-run trigger (default country Uganda)
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rerun'])) {
    $country = trim($_POST['country'] ?? 'Uganda');
    $pages = max(1, (int)($_POST['pages'] ?? 1));
    $script = realpath(__DIR__ . '/../scraper/therapyroute_scraper.php');
    $php = PHP_BINARY;
    $cmd = $php . ' ' . escapeshellarg($script) . ' --country=' . escapeshellarg($country) . ' --pages=' . escapeshellarg((string)$pages);
    // Run synchronously for simplicity; consider async for long runs
    $output = [];
    $exitCode = 0;
    exec($cmd . ' 2>&1', $output, $exitCode);
    $message = $exitCode === 0 ? 'Scrape started/completed successfully.' : ('Scrape failed. Exit code: ' . $exitCode);
}

// Fetch recent runs
$runs = $pdo->query('SELECT id, source, started_at, completed_at, new_count, updated_count, failed_count, notes FROM scrape_runs ORDER BY started_at DESC LIMIT 50')->fetchAll();
?>

<div class="container-fluid">
  <div class="row">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Scraper Runs</h1>
        <div>
          <form method="post" class="d-flex align-items-center">
            <input type="hidden" name="rerun" value="1">
            <label class="form-label me-2 mb-0">Country</label>
            <input type="text" name="country" class="form-control form-control-sm me-2" value="Uganda" style="width:160px;">
            <label class="form-label me-2 mb-0">Pages</label>
            <input type="number" name="pages" class="form-control form-control-sm me-2" value="1" min="1" max="10" style="width:90px;">
            <button type="submit" class="btn btn-sm btn-primary">Re-run Scrape</button>
          </form>
        </div>
      </div>

      <?php if (!empty($message)): ?>
        <div class="alert alert-info">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <div class="card">
        <div class="card-header">Recent Runs</div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Source</th>
                  <th>Started</th>
                  <th>Completed</th>
                  <th>New</th>
                  <th>Updated</th>
                  <th>Failed</th>
                  <th>Notes</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($runs as $r): ?>
                  <tr>
                    <td><?php echo (int)$r['id']; ?></td>
                    <td><?php echo htmlspecialchars($r['source']); ?></td>
                    <td><?php echo htmlspecialchars($r['started_at']); ?></td>
                    <td><?php echo htmlspecialchars($r['completed_at'] ?? ''); ?></td>
                    <td><?php echo (int)$r['new_count']; ?></td>
                    <td><?php echo (int)$r['updated_count']; ?></td>
                    <td><?php echo (int)$r['failed_count']; ?></td>
                    <td><?php echo htmlspecialchars($r['notes'] ?? ''); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>
  </div>

<?php require_once __DIR__ . '/../admin/includes/footer.php'; ?>