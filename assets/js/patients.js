// Dynamic patients list + appointments loader
async function fetchPatients(limit = 100) {
  const res = await fetch('backend/php/patients.php?limit=' + encodeURIComponent(limit));
  if (!res.ok) throw new Error('Failed to load patients');
  const json = await res.json();
  return json.patients || [];
}

async function fetchAppointmentsForPatient(patientId) {
  const res = await fetch('backend/php/appointments.php?patient_id=' + encodeURIComponent(patientId));
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
  return `<div class="appointments-list">${appointments.map(a => {
    const staff = (a.staff_first || a.staff_last) ? ` — ${a.staff_first || ''} ${a.staff_last || ''}` : '';
    return `<div class="appointment-item"><strong>${formatDateTime(a.appointment_time)}</strong>${staff}<div class="small muted">${a.department || ''} — ${a.status || ''}</div></div>`;
  }).join('')}</div>`;
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

    const rows = patients.map(p => {
      return `
        <div class="patient-row" data-patient-id="${p.id}">
          <div class="patient-main">
            <div class="col id">${p.id}</div>
            <div class="col name">${p.first_name} ${p.last_name}</div>
            <div class="col gender">${p.gender || ''}</div>
            <div class="col dob">${p.dob || ''}</div>
            <div class="col actions"><button class="btn small view-appointments" data-id="${p.id}">View appointments</button></div>
          </div>
          <div class="patient-appointments" style="display:none;padding-left:12px;margin-bottom:8px"></div>
        </div>`;
    }).join('');

    container.innerHTML = `<div class="patients-list">${rows}</div>`;

    // Attach handlers
    container.querySelectorAll('.view-appointments').forEach(btn => {
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

document.addEventListener('DOMContentLoaded', renderPatients);
