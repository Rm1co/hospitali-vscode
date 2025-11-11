// patients-login.js — small helper for the patient login page
(function(){
  const form = document.getElementById('login-form');
  const errorMsg = document.getElementById('error-msg');
  const themeToggle = document.getElementById('theme-toggle');
  const root = document.documentElement;

  // Safe guards
  if (!themeToggle) return;

  function applyTheme(theme){
    root.setAttribute('data-theme', theme);
    themeToggle.textContent = theme === 'dark' ? '☀️ Light' : '🌙 Dark Mode';
    themeToggle.setAttribute('aria-pressed', String(theme === 'dark'));
    localStorage.setItem('theme', theme);
  }

  // Initialize saved theme
  const saved = localStorage.getItem('theme') || root.getAttribute('data-theme') || 'light';
  applyTheme(saved);

  // Toggle handler
  themeToggle.addEventListener('click', () => {
    const current = root.getAttribute('data-theme') || 'light';
    const next = current === 'light' ? 'dark' : 'light';
    applyTheme(next);
  });

  // Keyboard accessibility for toggle (Space / Enter)
  themeToggle.addEventListener('keydown', (e) => {
    if (e.key === ' ' || e.key === 'Enter') {
      e.preventDefault();
      themeToggle.click();
    }
  });

  // Real login via backend
  if (form){
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      errorMsg.textContent = '';
      errorMsg.style.color = '';

      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value;

      if (!email || !password) {
        errorMsg.textContent = 'Please enter email and password.';
        errorMsg.style.color = '#dc2626';
        return;
      }

      try {
        const resp = await fetch('backend/php/login.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ email, password })
        });

        const json = await resp.json();
        if (resp.ok) {
          // Success — save basic info and redirect to dashboard
          localStorage.setItem('patient', JSON.stringify({ 
            account_id: json.account_id,
            patient_id: json.patient_id,
            email: json.email
          }));
          window.location.href = 'index.html';
          return;
        }

        errorMsg.textContent = json.message || 'Login failed';
        errorMsg.style.color = '#dc2626';
      } catch (err) {
        errorMsg.textContent = 'Network error';
        errorMsg.style.color = '#dc2626';
        console.error(err);
      }
    });
  }
})();
