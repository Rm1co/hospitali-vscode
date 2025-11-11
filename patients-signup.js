(function(){
  const form = document.getElementById('signup-form');
  const msg = document.getElementById('signup-msg');
  const themeToggle = document.getElementById('theme-toggle');
  const root = document.documentElement;
  const pwdInput = document.getElementById('password');
  const toggleBtn = document.getElementById('toggle-pwd');
  const strengthBar = document.getElementById('strength-bar');
  const strengthText = document.getElementById('strength-text');

  function applyTheme(theme){
    root.setAttribute('data-theme', theme);
    const btn = document.getElementById('theme-toggle');
    if(btn) btn.textContent = theme === 'dark' ? '☀️ Light' : '🌙 Dark Mode';
    localStorage.setItem('theme', theme);
  }

  const saved = localStorage.getItem('theme') || root.getAttribute('data-theme') || 'light';
  applyTheme(saved);
  if (themeToggle){
    themeToggle.addEventListener('click', () => {
      const current = root.getAttribute('data-theme') || 'light';
      const next = current === 'light' ? 'dark' : 'light';
      applyTheme(next);
    });
  }

  // Password strength meter
  function evaluateStrength(pwd){
    let score = 0;
    if (pwd.length >= 8) score++;
    if (pwd.length >= 12) score++;
    if (/[a-z]/.test(pwd)) score++;
    if (/[A-Z]/.test(pwd)) score++;
    if (/[0-9]/.test(pwd)) score++;
    if (/[^a-zA-Z0-9]/.test(pwd)) score++;
    return score;
  }

  if (pwdInput){
    pwdInput.addEventListener('input', () => {
      const pwd = pwdInput.value;
      const score = evaluateStrength(pwd);
      let strength = '', level = '';
      if (score < 2) {
        strength = 'Weak'; level = 'weak';
      } else if (score < 4) {
        strength = 'Fair'; level = 'fair';
      } else if (score < 6) {
        strength = 'Good'; level = 'good';
      } else {
        strength = 'Strong'; level = 'strong';
      }
      strengthBar.className = 'strength-bar ' + level;
      strengthText.textContent = pwd ? strength : '';
    });
  }

  // Show/hide password
  if (toggleBtn){
    toggleBtn.addEventListener('click', (e) => {
      e.preventDefault();
      const type = pwdInput.type === 'password' ? 'text' : 'password';
      pwdInput.type = type;
      toggleBtn.textContent = type === 'password' ? '👁️' : '👁️‍🗨️';
    });
  }

  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    msg.textContent = '';
    msg.style.color = '';

    const data = {
      first_name: document.getElementById('first_name').value.trim(),
      last_name: document.getElementById('last_name').value.trim(),
      email: document.getElementById('email').value.trim(),
      password: document.getElementById('password').value,
      dob: document.getElementById('dob').value || null,
      phone: document.getElementById('phone').value.trim() || null,
      address: document.getElementById('address').value.trim() || null
    };

    if (!data.email || !data.password || !data.first_name) {
      msg.textContent = 'Please fill required fields.';
      msg.style.color = '#dc2626';
      return;
    }

    if (evaluateStrength(data.password) < 3){
      msg.textContent = 'Password too weak. Use upper, lower, numbers, and special chars.';
      msg.style.color = '#dc2626';
      return;
    }

    try {
      const resp = await fetch('backend/php/register.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });

      const json = await resp.json();
      if (resp.ok) {
        msg.textContent = 'Account created. Redirecting...';
        msg.style.color = '#10b981';
        setTimeout(() => window.location.href = 'patients-login.html', 1200);
      } else {
        msg.textContent = json.message || 'Registration failed';
        msg.style.color = '#dc2626';
      }
    } catch (err) {
      msg.textContent = 'Network error';
      msg.style.color = '#dc2626';
      console.error(err);
    }
  });
})();
