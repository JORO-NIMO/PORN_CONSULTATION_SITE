(() => {
  const listEl = document.getElementById('therapist-list');

  async function fetchTherapists() {
    const res = await fetch('/api/therapists');
    const json = await res.json();
    renderList(json.data || []);
  }

  function fmtDate(d) { if (!d) return ''; return new Date(d).toLocaleDateString(); }

  function renderList(items) {
    listEl.innerHTML = '';
    if (!items.length) { listEl.innerHTML = '<p>No therapists found.</p>'; return; }
    items.forEach(t => {
      const el = document.createElement('div');
      el.className = 'content-list-item';
      el.innerHTML = `
        <div class="content-info">
          <h3>${escapeHtml(t.name || 'Unnamed')}</h3>
          <p>${escapeHtml(t.title || '')}</p>
          <p><strong>Specialties:</strong> ${escapeHtml(t.specialties || '')}</p>
          <p><strong>Location:</strong> ${escapeHtml([t.city, t.country].filter(Boolean).join(', '))}</p>
          <p><strong>Languages:</strong> ${escapeHtml(t.languages || '')}</p>
          <p><small>Last updated: ${fmtDate(t.updated_at || t.last_scraped)}</small></p>
        </div>
        <div class="content-stats" style="display:flex; flex-direction:column; gap:.5rem;">
          <a class="btn btn-secondary" href="${t.profile_url}" target="_blank">Source Profile</a>
          <a class="btn btn-primary" href="/therapist_edit.php">Claim your profile</a>
        </div>
      `;
      listEl.appendChild(el);
    });
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"]+/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]));
  }

  // Initial load
  fetchTherapists();
})();