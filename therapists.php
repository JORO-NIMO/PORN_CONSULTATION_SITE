<?php
require_once __DIR__ . '/config/config.php';
include __DIR__ . '/includes/header.php';
?>
<main class="container">
  <section class="page-hero" style="padding: 2rem 0;">
    <h1>Find a Therapist</h1>
    <p class="text-muted">Browse verified therapist profiles. Use filters to refine results.</p>
  </section>

  <section class="content-section">
    <div class="filter-bar" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1rem;">
      <input id="filter-city" class="form-input" type="text" placeholder="City">
      <input id="filter-country" class="form-input" type="text" placeholder="Country" value="Uganda">
      <input id="filter-specialty" class="form-input" type="text" placeholder="Specialty">
      <input id="filter-language" class="form-input" type="text" placeholder="Language">
    </div>
    <div style="margin-bottom: 1rem; display:flex; gap:.5rem;">
      <button id="apply-filters" class="btn btn-primary">Apply Filters</button>
      <button id="clear-filters" class="btn btn-secondary">Clear</button>
    </div>
    <div id="therapist-list" class="content-list"></div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="/assets/js/therapists.js"></script>
</body>
</html>