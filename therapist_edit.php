<?php
require_once __DIR__ . '/config.php';
include __DIR__ . '/includes/header.php';

$therapistId = isset($_COOKIE['therapist_id']) ? (int)$_COOKIE['therapist_id'] : 0;
?>
<main class="container">
  <section class="page-hero" style="padding: 2rem 0;">
    <h1>Edit Your Profile</h1>
    <?php if (!$therapistId): ?>
      <p class="text-muted">To edit, first claim your profile from the listing page and verify via email.</p>
    <?php else: ?>
      <p class="text-muted">You are editing profile #<?php echo $therapistId; ?>.</p>
    <?php endif; ?>
  </section>

  <?php if ($therapistId): ?>
  <section class="content-section">
    <form id="edit-form" class="auth-form" style="max-width: 720px;">
      <div class="form-group"><label>Name</label><input name="name" class="form-input" type="text"></div>
      <div class="form-group"><label>Title</label><input name="title" class="form-input" type="text"></div>
      <div class="form-group"><label>Specialties</label><textarea name="specialties" class="form-input" rows="4"></textarea></div>
      <div class="form-group"><label>City</label><input name="city" class="form-input" type="text"></div>
      <div class="form-group"><label>Country</label><input name="country" class="form-input" type="text"></div>
      <div class="form-group"><label>Languages</label><input name="languages" class="form-input" type="text"></div>
      <div class="form-group"><label>Contact Email</label><input name="contact_email" class="form-input" type="email"></div>
      <div class="form-group"><label>Phone</label><input name="phone" class="form-input" type="text"></div>
      <div style="display:flex; gap:.75rem;">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a class="btn btn-secondary" href="/therapists.php">Back to Listing</a>
      </div>
    </form>
  </section>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script>const TID = <?php echo json_encode($therapistId); ?>;</script>
<script src="/assets/js/therapists_edit.js"></script>
</body>
</html>