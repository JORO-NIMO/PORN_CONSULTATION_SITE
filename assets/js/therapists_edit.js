(() => {
  if (!window.TID) return; // Only run if a verified therapist cookie exists

  const form = document.getElementById('edit-form');

  async function load() {
    const res = await fetch('/api/therapists/' + TID);
    const json = await res.json();
    const t = json.data;
    ['name','title','specialties','city','country','languages','contact_email','phone'].forEach(f => {
      const el = form.querySelector(`[name="${f}"]`);
      if (el) el.value = t[f] || '';
    });
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = {};
    ['name','title','specialties','city','country','languages','contact_email','phone'].forEach(f => {
      const el = form.querySelector(`[name="${f}"]`);
      if (el && el.value.trim()) data[f] = el.value.trim();
    });
    const res = await fetch('/api/therapists/' + TID, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    const json = await res.json();
    if (json.ok) {
      alert('Profile updated successfully');
    } else {
      alert('Update failed: ' + (json.error || 'Unknown error'));
    }
  });

  load();
})();