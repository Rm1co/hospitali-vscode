const medicalQuotes = [
  { text: "Care is a commitment to action.", author: "Joan Halifax" },
  { text: "The art of medicine consists of amusing the patient while nature cures the disease.", author: "Voltaire" },
  { text: "Healing is a matter of time, but it is sometimes also a matter of opportunity.", author: "Hippocrates" },
  { text: "The greatest wealth is health.", author: "Virgil" },
  { text: "The best doctors and nurses are those who understand that health is not merely the absence of disease.", author: "Unknown" },
  { text: "To keep the body in good health is a duty... otherwise we shall not be able to keep our mind strong.", author: "Buddha" },
  { text: "Medicine is not only a science; it is also an art.", author: "Paracelsus" },
  { text: "Health is a state of complete physical, mental and social well-being.", author: "WHO" },
  { text: "Every patient carries her or his own doctor inside.", author: "Albert Schweitzer" },
  { text: "The good physician treats the disease; the great physician treats the patient who has the disease.", author: "William Osler" }
];

function switchTab(tab) {
  document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
  
  document.getElementById(tab + '-tab').classList.add('active');
  event.target.classList.add('active');
}

function refreshQuote() {
  const quote = medicalQuotes[Math.floor(Math.random() * medicalQuotes.length)];
  document.getElementById('quote-text').textContent = `"${quote.text}"`;
  document.getElementById('quote-author').textContent = `- ${quote.author}`;
}

function setupThemeToggle() {
  const toggles = [document.getElementById('theme-toggle'), document.getElementById('theme-toggle-2')];
  const html = document.documentElement;
  
  const savedTheme = localStorage.getItem('theme') || 'light';
  html.setAttribute('data-theme', savedTheme);
  updateThemeButtons();

  toggles.forEach(toggle => {
    if (toggle) {
      toggle.addEventListener('click', function() {
        const currentTheme = html.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeButtons();
      });
    }
  });
}

function updateThemeButtons() {
  const currentTheme = document.documentElement.getAttribute('data-theme');
  const toggles = [document.getElementById('theme-toggle'), document.getElementById('theme-toggle-2')];
  toggles.forEach(toggle => {
    if (toggle) toggle.textContent = currentTheme === 'light' ? '🌙' : '☀️';
  });
}

function checkPasswordStrength(password) {
  let strength = 0;
  const requirements = {
    length: password.length >= 8,
    uppercase: /[A-Z]/.test(password),
    lowercase: /[a-z]/.test(password),
    number: /\d/.test(password),
    special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
  };

  Object.values(requirements).forEach(req => {
    if (req) strength++;
  });

  updateRequirements(requirements);
  updateStrengthBar(strength);
  
  return requirements;
}

function updateRequirements(requirements) {
  const reqs = ['length', 'uppercase', 'lowercase', 'number', 'special'];
  reqs.forEach(req => {
    const el = document.getElementById(`req-${req}`);
    if (requirements[req]) {
      el.classList.add('met');
    } else {
      el.classList.remove('met');
    }
  });
}

function updateStrengthBar(strength) {
  const bar = document.getElementById('strength-bar');
  const label = document.getElementById('strength-label');
  const text = document.getElementById('strength-text');
  
  const strengths = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
  const colors = ['#dc2626', '#f59e0b', '#3b82f6', '#3b82f6', '#10b981', '#10b981'];
  const classNames = ['weak', 'weak', 'fair', 'good', 'strong', 'strong'];
  
  bar.style.width = (strength * 16.66) + '%';
  bar.style.backgroundColor = colors[strength];
  label.textContent = strengths[strength];
  
  text.className = 'strength-text ' + classNames[strength];
}

document.addEventListener('input', function(e) {
  if (e.target.id === 'signup-password') {
    const strengthContainer = document.getElementById('strength-container');
    const requirementsContainer = document.getElementById('requirements-container');
    const password = e.target.value;
    
    if (password.length > 0) {
      strengthContainer.style.display = 'block';
      requirementsContainer.style.display = 'block';
      checkPasswordStrength(password);
    } else {
      strengthContainer.style.display = 'none';
      requirementsContainer.style.display = 'none';
    }
  }
});

const codeDigits = document.querySelectorAll('.code-digit');
codeDigits.forEach((input, index) => {
  input.addEventListener('input', function() {
    if (this.value.length === 1 && index < codeDigits.length - 1) {
      codeDigits[index + 1].focus();
    }
  });

  input.addEventListener('keydown', function(e) {
    if (e.key === 'Backspace' && this.value === '' && index > 0) {
      codeDigits[index - 1].focus();
    }
  });
});

let verificationCode = null;
let codeExpiry = null;

async function sendVerificationCode(e) {
  e.preventDefault();
  
  const email = document.getElementById('signup-email').value;
  const btn = document.getElementById('send-code-btn');
  
  if (!email) {
    alert('Please enter an email address first');
    return;
  }

  btn.disabled = true;
  btn.textContent = 'Sending...';

  try {
    const response = await fetch('./backend/php/send-verification-code.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: email })
    });

    const data = await response.json();
    
    if (data.success) {
      verificationCode = data.code;
      codeExpiry = Date.now() + (5 * 60 * 1000); // 5 minutes
      document.getElementById('code-input-container').style.display = 'flex';
      btn.textContent = 'Code sent! Check email';
      document.querySelector('.code-digit').focus();
    } else {
      alert('Error sending code: ' + data.message);
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error sending verification code');
  } finally {
    btn.disabled = false;
  }
}

function getEnteredCode() {
  return Array.from(codeDigits).map(input => input.value).join('');
}

function verifyCode() {
  if (Date.now() > codeExpiry) {
    alert('Verification code expired. Please request a new one.');
    return false;
  }

  const enteredCode = getEnteredCode();
  if (enteredCode === verificationCode.toString()) {
    document.getElementById('verification-success').classList.add('show');
    return true;
  } else {
    alert('Invalid verification code');
    return false;
  }
}

document.getElementById('login-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const email = document.getElementById('login-email').value;
  const password = document.getElementById('login-password').value;
  const errorMsg = document.getElementById('login-error-msg');
  
  try {
    const response = await fetch('./backend/php/login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password })
    });

    const data = await response.json();
    if (data.success) {
      localStorage.setItem('patient', JSON.stringify({
        account_id: data.account_id,
        patient_id: data.patient_id,
        email: data.email
      }));
      window.location.href = 'patients.html';
    } else {
      errorMsg.textContent = data.message || 'Login failed';
    }
  } catch (error) {
    console.error('Error:', error);
    errorMsg.textContent = 'Network error';
  }
});

document.getElementById('signup-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const firstName = document.getElementById('first-name').value;
  const lastName = document.getElementById('last-name').value;
  const email = document.getElementById('signup-email').value;
  const password = document.getElementById('signup-password').value;
  const errorMsg = document.getElementById('signup-error-msg');

  // Check password requirements
  const requirements = checkPasswordStrength(password);
  if (!Object.values(requirements).every(v => v)) {
    errorMsg.textContent = 'Password does not meet requirements';
    return;
  }

  if (!verifyCode()) {
    return;
  }

  try {
    const response = await fetch('./backend/php/register.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        first_name: firstName,
        last_name: lastName,
        email: email,
        password: password
      })
    });

    const data = await response.json();
    if (data.success) {
      alert('Account created successfully! Please login.');
      switchTab('login');
      document.getElementById('login-email').value = email;
    } else {
      errorMsg.textContent = data.message || 'Registration failed';
    }
  } catch (error) {
    console.error('Error:', error);
    errorMsg.textContent = 'Network error';
  }
});

setupThemeToggle();
refreshQuote();
