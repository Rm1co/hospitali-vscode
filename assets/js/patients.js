// Success message helper
function showSuccessMessage(message) {
  const msg = document.createElement('div');
  msg.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: #4caf50;
    color: white;
    padding: 16px 24px;
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    z-index: 99999;
    font-weight: 600;
    font-size: 16px;
    animation: slideIn 0.3s ease;
  `;
  msg.textContent = message;

  const style = document.createElement('style');
  style.textContent = `
    @keyframes slideIn {
      from { transform: translateX(400px); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
      from { transform: translateX(0); opacity: 1; }
      to { transform: translateX(400px); opacity: 0; }
    }
  `;
  if (!document.querySelector('style[data-success-msg]')) {
    style.setAttribute('data-success-msg', 'true');
    document.head.appendChild(style);
  }

  document.body.appendChild(msg);

  setTimeout(() => {
    msg.style.animation = 'slideOut 0.3s ease';
    setTimeout(() => msg.remove(), 300);
  }, 3000);
}

// Dynamic patients list + appointments loader
async function fetchPatients(limit = 100) {
  const res = await fetch('backend/php/patients.php?limit=' + encodeURIComponent(limit));
  if (!res.ok) throw new Error('Failed to load patients');
  const json = await res.json();
  return json.patients || [];
}

async function fetchAppointmentsForPatient(patientId) {
  const res = await fetch(
    'backend/php/appointments.php?patient_id=' + encodeURIComponent(patientId)
  );
  if (!res.ok) throw new Error('Failed to load appointments');
  const json = await res.json();
  return json.appointments || [];
}

function formatDateTime(dt) {
  if (!dt) return '';
  const d = new Date(dt);
  return d.toLocaleString();
}

function createAppointmentsRow(appointments) {
  if (!appointments || !appointments.length) return '<div class="muted">No appointments</div>';
  return `<div class="appointments-list">${appointments
    .map((a) => {
      const staff =
        a.staff_first || a.staff_last ? ` — ${a.staff_first || ''} ${a.staff_last || ''}` : '';
      return `<div class="appointment-item"><strong>${formatDateTime(
        a.appointment_time
      )}</strong>${staff}<div class="small muted">${a.department || ''} — ${
        a.status || ''
      }</div></div>`;
    })
    .join('')}</div>`;
}

async function renderPatients() {
  const container = document.getElementById('patientsTable');
  if (!container) return;
  container.innerHTML = '<div class="loading">Loading patients...</div>';

  try {
    const patients = await fetchPatients();
    if (!patients.length) {
      container.innerHTML = '<div class="placeholder">No patients found</div>';
      return;
    }

    const rows = patients
      .map((p) => {
        return `
        <div class="patient-row" data-patient-id="${p.id}">
          <div class="patient-main">
            <div class="col id">${p.id}</div>
            <div class="col name">${p.first_name} ${p.last_name}</div>
            <div class="col gender">${p.gender || ''}</div>
            <div class="col dob">${p.dob || ''}</div>
            <div class="col actions"><button class="btn small view-appointments" data-id="${
              p.id
            }">View appointments</button></div>
          </div>
          <div class="patient-appointments" style="display:none;padding-left:12px;margin-bottom:8px"></div>
        </div>`;
      })
      .join('');

    container.innerHTML = `<div class="patients-list">${rows}</div>`;

    // Attach handlers
    container.querySelectorAll('.view-appointments').forEach((btn) => {
      btn.addEventListener('click', async (e) => {
        const id = btn.getAttribute('data-id');
        const parent = btn.closest('.patient-row');
        const target = parent.querySelector('.patient-appointments');
        if (target.style.display === 'none' || target.innerHTML.trim() === '') {
          // load appointments
          btn.textContent = 'Loading...';
          try {
            const appts = await fetchAppointmentsForPatient(id);
            target.innerHTML = createAppointmentsRow(appts);
            target.style.display = 'block';
            btn.textContent = 'Hide appointments';
          } catch (err) {
            target.innerHTML = '<div class="error">Failed to load appointments</div>';
            target.style.display = 'block';
            btn.textContent = 'View appointments';
          }
        } else {
          // hide
          target.style.display = 'none';
          btn.textContent = 'View appointments';
        }
      });
    });
  } catch (err) {
    console.error(err);
    container.innerHTML = '<div class="error">Failed to load patients</div>';
  }
}

function openPatientForm() {
  const container = document.getElementById('patientsTable');
  if (!container) return;

  container.style.display = 'none';

  const formWrap = document.createElement('div');
  formWrap.id = 'patient-add-form-wrap';
  formWrap.innerHTML = `
    <form style="max-width:700px;margin:20px auto;">
      <div style="display:flex;flex-direction:column;gap:12px;padding:20px;background:white;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
        <h3 style="margin:0;margin-bottom:10px;">Add New Patient</h3>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <label>
            <span style="display:block;margin-bottom:4px;font-weight:500;">First Name *</span>
            <input type="text" name="first_name" required placeholder="John" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;">
          </label>

          <label>
            <span style="display:block;margin-bottom:4px;font-weight:500;">Last Name *</span>
            <input type="text" name="last_name" required placeholder="Doe" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;">
          </label>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <label>
            <span style="display:block;margin-bottom:4px;font-weight:500;">Date of Birth</span>
            <input type="date" name="dob" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;">
          </label>

          <label>
            <span style="display:block;margin-bottom:4px;font-weight:500;">Gender</span>
            <select name="gender" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;">
              <option value="">-- Select --</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
              <option value="Other">Other</option>
            </select>
          </label>
        </div>

        <label>
          <span style="display:block;margin-bottom:4px;font-weight:500;">Phone</span>
          <input type="tel" name="phone" placeholder="+1234567890" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;">
        </label>

        <label>
          <span style="display:block;margin-bottom:4px;font-weight:500;">Address</span>
          <textarea name="address" rows="3" placeholder="123 Main St, City, Country" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;font-family:inherit;"></textarea>
        </label>

        <div style="display:flex;gap:10px;margin-top:10px;">
          <button type="submit" class="btn" style="flex:1;">Add Patient</button>
          <button type="button" id="patient-add-cancel" class="btn" style="flex:1;background:#999;">Cancel</button>
        </div>
      </div>
    </form>
  `;

  container.parentNode.insertBefore(formWrap, container);

  const form = formWrap.querySelector('form');
  const cancelBtn = document.getElementById('patient-add-cancel');

  const cleanup = () => {
    const wrap = document.getElementById('patient-add-form-wrap');
    if (wrap) wrap.remove();
    container.style.display = '';
  };

  cancelBtn.addEventListener('click', (e) => {
    e.preventDefault();
    cleanup();
  });

  form.onsubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    const data = {
      first_name: formData.get('first_name'),
      last_name: formData.get('last_name'),
      dob: formData.get('dob') || null,
      gender: formData.get('gender') || null,
      phone: formData.get('phone') || null,
      address: formData.get('address') || null,
    };

    try {
      const response = await fetch('backend/php/patients.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
      });

      const result = await response.json();
      if (response.ok) {
        cleanup();
        showSuccessMessage('Patient added successfully!');
        await renderPatients();
      } else {
        alert('Error: ' + (result.message || 'Failed to add patient'));
      }
    } catch (error) {
      console.error('Failed to add patient:', error);
      alert('Failed to add patient');
    }
  };
}

document.addEventListener('DOMContentLoaded', renderPatients);
