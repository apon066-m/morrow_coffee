// Mobile nav toggle + client-side form validation (server-side validation is still enforced in PHP)
document.addEventListener('DOMContentLoaded', () => {
  const t = document.querySelector('.mobile-toggle'), n = document.getElementById('nav-links');
  if (t && n) t.addEventListener('click', () => t.setAttribute('aria-expanded', n.classList.toggle('open')));

  const rules = {
    name: v => v.trim().length >= 2 || 'Enter a name with at least 2 characters.',
    email: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) || 'Enter a valid email address, like name@example.com.',
    password: v => v.length >= 6 || 'Password must be at least 6 characters.',
    category: v => v.trim().length >= 2 || 'Choose a category.',
    description: v => v.trim().length >= 10 || 'Description needs at least 10 characters.',
    price: v => (parseFloat(v) > 0 && parseFloat(v) <= 100) || 'Enter a price between $0.01 and $100.'
  };
  const show = (el, msg) => {
    let box = el.parentElement.querySelector('.err');
    if (!box) { box = document.createElement('span'); box.className = 'err'; box.setAttribute('role', 'alert'); el.parentElement.appendChild(box); }
    box.textContent = msg || ''; el.classList.toggle('invalid', !!msg); el.setAttribute('aria-invalid', msg ? 'true' : 'false');
  };
  const check = el => { const r = rules[el.name]; if (!r) return true; const res = r(el.value); show(el, res === true ? '' : res); return res === true; };
  document.querySelectorAll('form[data-validate]').forEach(f => {
    f.noValidate = true;
    f.querySelectorAll('input,textarea,select').forEach(el => el.addEventListener('blur', () => check(el)));
    f.addEventListener('submit', ev => {
      let bad = null; f.querySelectorAll('input,textarea,select').forEach(el => { if (!check(el) && !bad) bad = el; });
      if (bad) { ev.preventDefault(); bad.focus(); }
    });
  });

  // Image upload: shrink big photos in the browser (max 1400px JPEG), check type/size, show live preview
  document.querySelectorAll('input[type=file][data-max]').forEach(inp => {
    inp.addEventListener('change', async () => {
      let f = inp.files[0]; const prev = document.getElementById('imgPreview');
      if (!f) return;
      if (!/^image\/(jpeg|png|webp)$/.test(f.type)) { show(inp, 'Please choose a JPG, PNG or WebP image (iPhone HEIC photos: export as JPG first).'); inp.value = ''; return; }
      try {
        const bmp = await createImageBitmap(f, { imageOrientation: 'from-image' });
        const sc = Math.min(1, 1400 / bmp.width), c = document.createElement('canvas');
        c.width = Math.round(bmp.width * sc); c.height = Math.round(bmp.height * sc);
        const ctx = c.getContext('2d'); ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, c.width, c.height); ctx.drawImage(bmp, 0, 0, c.width, c.height);
        const blob = await new Promise(r => c.toBlob(r, 'image/jpeg', 0.86));
        if (blob && blob.size < f.size) { const dt = new DataTransfer(); dt.items.add(new File([blob], 'photo.jpg', { type: 'image/jpeg' })); inp.files = dt.files; f = inp.files[0]; }
      } catch (e) { /* browser can't resize: upload the original and let the server handle it */ }
      if (f.size > +inp.dataset.max) { show(inp, 'That image is still over 8 MB. Choose a smaller one.'); inp.value = ''; return; }
      show(inp, ''); if (prev) prev.src = URL.createObjectURL(f);
    });
  });
});
