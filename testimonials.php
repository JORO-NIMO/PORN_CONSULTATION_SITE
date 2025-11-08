<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/api_helpers.php';

// Prepare paired content for sections
$affirmations = getAffirmations(2);
$zen = getZenQuotes(2);
$quotes_data = getQuotableQuotes(2);
$wiki_pairs = getWikipediaSummaries(null, 2);
$guardian_pairs = getGuardianArticles(2);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recovery Reflections - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <main class="testimonials-page">
<div class="container">
        <!-- CardSwap: two items per section -->
        <div style="height: 600px; position: relative; margin-bottom: 2rem;">
          <div class="cardswap" data-card-distance="60" data-vertical-distance="70" data-delay="5000" data-pause-on-hover="false">
            <div class="cs-card">
              <h3>Affirmations</h3>
              <?php foreach ($affirmations as $a): ?>
                <p>“<?php echo htmlspecialchars($a); ?>”</p>
              <?php endforeach; ?>
            </div>
            <div class="cs-card">
              <h3>ZenQuotes</h3>
              <?php foreach ($zen as $z): ?>
                <p><?php echo htmlspecialchars($z['q'] ?? ''); ?> — <em><?php echo htmlspecialchars($z['a'] ?? ''); ?></em></p>
              <?php endforeach; ?>
            </div>
            <div class="cs-card">
              <h3>Quotable</h3>
              <?php foreach ($quotes_data as $q): ?>
                <p><?php echo htmlspecialchars($q['content'] ?? ''); ?> — <em><?php echo htmlspecialchars($q['author'] ?? ''); ?></em></p>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Wikipedia (pair) -->
        <section style="margin-bottom: 2rem;">
          <h2>Wikipedia Snapshot</h2>
          <?php foreach ($wiki_pairs as $w): ?>
            <div class="card" style="margin-bottom:1rem;">
              <div class="card-body">
                <h3 class="card-title" style="margin-top:0;"><?php echo htmlspecialchars($w['title']); ?></h3>
                <p class="card-text"><?php echo htmlspecialchars($w['extract']); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </section>

        <!-- Guardian (pair) -->
        <section style="margin-bottom: 2rem;">
          <h2>Latest from The Guardian</h2>
          <?php foreach ($guardian_pairs as $g): ?>
            <div class="card" style="margin-bottom:1rem;">
              <div class="card-body">
                <h3 class="card-title" style="margin-top:0;"><?php echo htmlspecialchars($g['webTitle'] ?? ($g['fields']['trailText'] ?? 'Article')); ?></h3>
                <p class="card-text"><?php echo htmlspecialchars($g['fields']['trailText'] ?? ''); ?></p>
                <a class="btn btn-link" href="<?php echo htmlspecialchars($g['webUrl'] ?? '#'); ?>" target="_blank">Read more</a>
              </div>
            </div>
          <?php endforeach; ?>
        </section>
</div>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
