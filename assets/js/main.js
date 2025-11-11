// ==============================
// 🏥 HOSPITAL FRONTEND MAIN LOGIC
// ==============================

// New patient and appointment modal handlers
function newPatient() {
  document.getElementById("newPatientModal").style.display = "grid";
}

function closePatientModal() {
  document.getElementById("newPatientModal").style.display = "none";
  document.getElementById("newPatientForm").reset();
}

function savePatient(event) {
  event.preventDefault();
  const btn = event.target.querySelector('button[type="submit"]');
  const originalText = btn.textContent;
  btn.disabled = true;
  btn.textContent = 'Saving...';

  const data = {
    first_name: document.getElementById('patientFirstName').value.trim(),
    last_name: document.getElementById('patientLastName').value.trim(),
    dob: document.getElementById('patientDOB').value || null,
    gender: document.getElementById('patientGender').value || null,
    phone: document.getElementById('patientPhone').value.trim() || null,
    address: document.getElementById('patientAddress').value.trim() || null
  };

  if (!data.first_name || !data.last_name) {
    alert('First and last name are required');
    btn.disabled = false;
    btn.textContent = originalText;
    return;
  }

  fetch('backend/php/patients.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  })
  .then(resp => {
    if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
    return resp.json();
  })
  .then(json => {
    alert('Patient added successfully! ID: ' + json.id);
    closePatientModal();
    // TODO: Refresh patient list
  })
  .catch(err => {
    alert('Error: ' + err.message);
    console.error(err);
  })
  .finally(() => {
    btn.disabled = false;
    btn.textContent = originalText;
  });
}

function newAppointment() {
  document.getElementById("newAppointmentModal").style.display = "grid";
}

function closeAppointmentModal() {
  document.getElementById("newAppointmentModal").style.display = "none";
  document.getElementById("newAppointmentForm").reset();
}

function saveAppointment(event) {
  event.preventDefault();
  const data = {
    patient_id: document.getElementById('appointmentPatientId').value,
    staff_id: null,
    appointment_time: document.getElementById('appointmentTime').value,
    department: document.getElementById('appointmentDepartment').value,
    status: document.getElementById('appointmentStatus').value
  };
  // TODO: POST to backend/php/appointments.php
  console.log('Save appointment:', data);
  closeAppointmentModal();
}

// Close modals when clicking outside
document.addEventListener('click', (e) => {
  const patientModal = document.getElementById('newPatientModal');
  const appointmentModal = document.getElementById('newAppointmentModal');
  if (e.target === patientModal) closePatientModal();
  if (e.target === appointmentModal) closeAppointmentModal();
});

// Keyboard shortcut: 'g' + 'd' focuses the dashboard link
(function () {
  let seq = '';
  window.addEventListener('keydown', e => {
    seq += e.key.toLowerCase();
    if (seq.endsWith('gd')) {
      const el = document.querySelector('nav a');
      if (el) el.focus();
      seq = '';
    }
    if (seq.length > 4) seq = seq.slice(-4);
  });
})();

// ==============================
// 📤 CSV EXPORT UTILITY
// ==============================
function exportCSVFromRows(filename, header, rows) {
  const csv = [header, ...rows]
    .map(r => r.map(cell => '"' + String(cell).replace(/"/g, '""') + '"').join(','))
    .join('\n');
  const blob = new Blob([csv], { type: 'text/csv' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  a.click();
  URL.revokeObjectURL(url);
}

// ==============================
// 📊 SIMPLE ADMISSIONS CHART
// ==============================
// Simple admissions chart drawing (canvas) used by index page
function drawAdmissionsChart(canvas, data) {
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const dpr = window.devicePixelRatio || 1;
  const w = canvas.width;
  const h = canvas.height;
  canvas.width = w * dpr;
  canvas.height = h * dpr;
  canvas.style.width = w + 'px';
  canvas.style.height = h + 'px';
  ctx.scale(dpr, dpr);
  ctx.clearRect(0, 0, w, h);

  const padding = 24;
  const max = Math.max(...data);
  const min = Math.min(...data);
  const range = Math.max(1, max - min);
  const stepX = (w - padding * 2) / (data.length - 1);

  // 🎨 Detect current theme colors from CSS variables
  const styles = getComputedStyle(document.body);
  const textColor = styles.getPropertyValue('--text-color').trim() || '#222';
  const accentColor = styles.getPropertyValue('--accent-color').trim() || '#0f766e';
  const bgColor = styles.getPropertyValue('--bg-color').trim() || '#fff';

  // Adjust stroke for visibility in dark mode
  ctx.strokeStyle = accentColor;
  ctx.lineWidth = 2;
  ctx.beginPath();
  data.forEach((v, i) => {
    const x = padding + i * stepX;
    const y = padding + ((max - v) / range) * (h - padding * 2);
    if (i === 0) ctx.moveTo(x, y);
    else ctx.lineTo(x, y);
  });
  ctx.stroke();

  // Area fill (uses accent color transparency)
  ctx.lineTo(w - padding, h - padding);
  ctx.lineTo(padding, h - padding);
  ctx.closePath();
  const grad = ctx.createLinearGradient(0, 0, 0, h);
  grad.addColorStop(0, accentColor + '22'); // translucent top
  grad.addColorStop(1, bgColor + '00'); // fade to bg
  ctx.fillStyle = grad;
  ctx.fill();

  // Dots
  data.forEach((v, i) => {
    const x = padding + i * stepX;
    const y = padding + ((max - v) / range) * (h - padding * 2);
    ctx.beginPath();
    ctx.fillStyle = bgColor;
    ctx.strokeStyle = accentColor;
    ctx.lineWidth = 1.6;
    ctx.arc(x, y, 4, 0, Math.PI * 2);
    ctx.fill();
    ctx.stroke();
  });

  // Labels (days)
  ctx.fillStyle = textColor;
  ctx.font = '12px system-ui, Arial';
  ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].forEach((t, i) => {
    const x = padding + i * stepX;
    ctx.fillText(t, x - 10, h - 6);
  });
}
// ==============================
// ⚙️ INITIALIZATION ON PAGE LOAD
// ==============================
document.addEventListener('DOMContentLoaded', () => {
  const yearEl = document.getElementById('year');
  if (yearEl) yearEl.textContent = new Date().getFullYear();

  // Appointments demo data
  const appointmentsBody = document.getElementById('appointmentsBody');
  if (appointmentsBody && window._demoAppointments) {
    appointmentsBody.innerHTML = '';
    window._demoAppointments.forEach(a => {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${a.time}</td><td>${a.patient}</td><td>${a.doctor}</td><td>${a.dept}</td><td><span class="pill ${
        a.status === 'Confirmed'
          ? 'success'
          : a.status === 'Walk-in'
          ? 'info'
          : 'warn'
      }">${a.status}</span></td>`;
      appointmentsBody.appendChild(tr);
    });
  }

  // Draw admissions chart if present
  const chart = document.getElementById('admissionsChart');
  if (chart) drawAdmissionsChart(chart, [12, 18, 9, 15, 22, 19, 14]);

  // Initialize theme toggle
  const themeToggle = document.getElementById('theme-toggle');
  if (themeToggle) {
    const body = document.body;
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
      body.setAttribute('data-theme', savedTheme);
      themeToggle.textContent =
        savedTheme === 'dark' ? '☀️ Light Mode' : '🌙 Dark Mode';
    }

    themeToggle.addEventListener('click', () => {
      const currentTheme = body.getAttribute('data-theme');
      const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
      body.setAttribute('data-theme', newTheme);
      localStorage.setItem('theme', newTheme);
      themeToggle.textContent =
        newTheme === 'dark' ? '☀️ Light Mode' : '🌙 Dark Mode';
    });
  }
});
