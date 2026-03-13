document.addEventListener('DOMContentLoaded', function () {
  const getCsrfToken = function(){
    const m = document.querySelector('meta[name="csrf-token"]');
    if (m && m.getAttribute('content')) return m.getAttribute('content');
    if (window.Laravel && window.Laravel.csrfToken) return window.Laravel.csrfToken;
    const c = document.cookie.match('(^|;)\\s*XSRF-TOKEN\\s*=\\s*([^;]+)');
    return c ? decodeURIComponent(c.pop()) : '';
  };
  const csrf = getCsrfToken();
  const allowedKeys = ['company_logo','signature_president','signature_instructor','signature_collaborator'];

  allowedKeys.forEach(key => {
    const canvas = document.getElementById('canvas-' + key);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    let drawing = false;
    let last = { x: 0, y: 0 };

    canvas.addEventListener('pointerdown', (e) => {
      drawing = true;
      const rect = canvas.getBoundingClientRect();
      last.x = e.clientX - rect.left;
      last.y = e.clientY - rect.top;
    });

    canvas.addEventListener('pointermove', (e) => {
      if (!drawing) return;
      const rect = canvas.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      ctx.beginPath();
      ctx.moveTo(last.x, last.y);
      ctx.lineTo(x, y);
      ctx.stroke();
      last.x = x;
      last.y = y;
    });

    const stop = () => drawing = false;
    canvas.addEventListener('pointerup', stop);
    canvas.addEventListener('pointerleave', stop);

    // clear button
    document.querySelectorAll('.clear-signature').forEach(btn => {
      if (btn.dataset.key !== key) return;
      btn.addEventListener('click', () => ctx.clearRect(0, 0, canvas.width, canvas.height));
    });

    // save button
    document.querySelectorAll('.save-signature').forEach(btn => {
      if (btn.dataset.key !== key) return;
      btn.addEventListener('click', async () => {
        const dataUrl = canvas.toDataURL('image/png');
        try {
          const res = await fetch('/admin/certificate-assets/store', {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': csrf,
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            },
            body: JSON.stringify({ key: key, image: dataUrl })
          });
          if (!res.ok) throw new Error('Save failed');
          const json = await res.json();
          const img = document.getElementById('preview-' + key);
          if (img && json.url) img.src = json.url + '?t=' + Date.now();
          const sourceLabel = document.getElementById('source-' + key);
          if (sourceLabel) sourceLabel.textContent = 'Saved: drawn';
        } catch (err) {
          alert('Error saving signature: ' + err.message);
        }
      });
    });

    // upload button
    document.querySelectorAll('.upload-btn').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        const key = e.currentTarget.dataset.key;
        const input = document.getElementById('file-' + key || 'file-' + key);
        // special-case company_logo which uses id file-company_logo
        const fileInput = document.getElementById('file-' + key) || document.getElementById('file-' + key.replace('signature_','')) || document.getElementById('file-' + key);
        if (!fileInput || fileInput.files.length === 0) { alert('Choose a file first'); return; }
        const formData = new FormData();
        formData.append('key', key);
        formData.append('file', fileInput.files[0]);

        try {
          const res = await fetch('/admin/certificate-assets/store', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf },
            body: formData
          });
          if (!res.ok) throw new Error('Upload failed');
          const json = await res.json();
          const img = document.getElementById('preview-' + key);
          if (img && json.url) img.src = json.url + '?t=' + Date.now();
          const sourceLabel = document.getElementById('source-' + key);
          if (sourceLabel) sourceLabel.textContent = 'Saved: upload';
        } catch (err) {
          alert('Upload failed: ' + err.message);
        }
      });
    });

  });
});
