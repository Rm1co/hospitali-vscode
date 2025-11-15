const themeToggle = document.getElementById('theme-toggle');
const html = document.documentElement;

const savedTheme = localStorage.getItem('theme') || 'light';
html.setAttribute('data-theme', savedTheme);
updateThemeButton();

themeToggle.addEventListener('click', function () {
  const currentTheme = html.getAttribute('data-theme');
  const newTheme = currentTheme === 'light' ? 'dark' : 'light';

  html.setAttribute('data-theme', newTheme);
  localStorage.setItem('theme', newTheme);
  updateThemeButton();
});

function updateThemeButton() {
  const currentTheme = html.getAttribute('data-theme');
  themeToggle.textContent = currentTheme === 'light' ? '🌙 Dark' : '☀️ Light';
}

// Page Navigation
const navLinks = document.querySelectorAll('.nav-link');
const pages = document.querySelectorAll('.page');

navLinks.forEach((link) => {
  link.addEventListener('click', (e) => {
    e.preventDefault();
    const pageName = link.dataset.page;

    navLinks.forEach((l) => l.classList.remove('active'));
    link.classList.add('active');

    pages.forEach((p) => (p.style.display = 'none'));
    document.getElementById(`${pageName}-page`).style.display = 'block';

    if (pageName === 'appointments') {
      loadAppointments();
    } else if (pageName === 'doctors') {
      loadAllDoctors();
    } else if (pageName === 'requests') {
      loadAppointmentRequests();
    }
  });
});

// Load appointment requests
async function loadAppointmentRequests() {
  const container = document.getElementById('requests-container');
  container.innerHTML = '<div class="loading"><div class="spinner"></div>Loading requests...</div>';

  try {
    const response = await fetch('./backend/php/get-appointment-requests.php?status=Pending');
    const data = await response.json();

    if (data.success) {
      renderAppointmentRequests(data.requests);
    } else {
      container.innerHTML = `<div class="empty-state"><i class='bx bxs-error'></i><p>Error: ${data.message}</p></div>`;
    }
  } catch (error) {
    console.error('Error:', error);
    container.innerHTML = `<div class="empty-state"><i class='bx bxs-error'></i><p>Error loading requests</p></div>`;
  }
}

function renderAppointmentRequests(requests) {
  const container = document.getElementById('requests-container');

  if (!requests || requests.length === 0) {
    container.innerHTML = `
      <div class="empty-state">
        <i class='bx bxs-inbox'></i>
        <p>No pending appointment requests</p>
      </div>
    `;
    return;
  }

  container.innerHTML = requests
    .map(
      (req) => `
    <div class="request-card" style="border: 1px solid var(--border); border-radius: 8px; padding: 20px; margin-bottom: 15px; background: var(--bg);">
      <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
        <div>
          <h4 style="margin: 0 0 5px 0;">${req.patient_name}</h4>
          <p style="margin: 0; color: var(--text-secondary); font-size: 14px;">
            <i class='bx bxs-phone'></i> ${req.patient_phone || 'N/A'}
          </p>
        </div>
        <span style="background: #fef3c7; color: #92400e; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
          PENDING
        </span>
      </div>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-bottom: 15px;">
        <div>
          <strong style="color: var(--text-secondary); font-size: 13px;">Department:</strong>
          <p style="margin: 5px 0 0 0;">${req.department}</p>
        </div>
        <div>
          <strong style="color: var(--text-secondary); font-size: 13px;">Preferred Date:</strong>
          <p style="margin: 5px 0 0 0;">${new Date(req.preferred_date).toLocaleDateString()}</p>
        </div>
        <div>
          <strong style="color: var(--text-secondary); font-size: 13px;">Preferred Time:</strong>
          <p style="margin: 5px 0 0 0;">${req.preferred_time}</p>
        </div>
      </div>
      <div style="margin-bottom: 15px;">
        <strong style="color: var(--text-secondary); font-size: 13px;">Reason:</strong>
        <p style="margin: 5px 0 0 0;">${req.reason}</p>
      </div>
      <button onclick="scheduleFromRequest(${req.id}, ${req.patient_id}, '${req.patient_name}', '${
        req.department
      }')" 
              class="btn btn-primary" style="width: 100%;">
        <i class='bx bxs-calendar-check'></i> Schedule Appointment
      </button>
    </div>
  `
    )
    .join('');
}

let currentRequestId = null;
let currentRequestDepartment = null;

function scheduleFromRequest(requestId, patientId, patientName, department) {
  currentRequestId = requestId;
  currentRequestDepartment = department;

  // Switch to allocate page
  document.querySelectorAll('.nav-link').forEach((l) => l.classList.remove('active'));
  document.querySelector('[data-page="allocate"]').classList.add('active');
  document.querySelectorAll('.page').forEach((p) => (p.style.display = 'none'));
  document.getElementById('allocate-page').style.display = 'block';

  // Pre-fill patient
  const patientSelect = document.getElementById('patient-select');
  for (let i = 0; i < patientSelect.options.length; i++) {
    if (patientSelect.options[i].value == patientId) {
      patientSelect.selectedIndex = i;
      break;
    }
  }

  // Pre-fill department
  document.getElementById('department-select').value = department;

  // Filter doctors by department
  loadAvailableDoctorsByDepartment(department);

  // Scroll to top
  window.scrollTo(0, 0);
}

async function loadAvailableDoctorsByDepartment(department) {
  const container = document.getElementById('doctors-container');
  container.innerHTML = '<div class="loading"><div class="spinner"></div>Loading doctors...</div>';

  try {
    const response = await fetch('./backend/php/get-available-doctors.php');
    const data = await response.json();

    if (data.success) {
      // Filter doctors by department
      const filteredDoctors = data.doctors.filter((doctor) => doctor.department === department);
      renderDoctorsList(filteredDoctors, department);
    } else {
      console.error('Error loading doctors:', data.message);
    }
  } catch (error) {
    console.error('Error:', error);
  }
}

async function loadAvailableDoctors() {
  try {
    const response = await fetch('./backend/php/get-available-doctors.php');
    const data = await response.json();

    if (data.success) {
      renderDoctorsList(data.doctors);
    } else {
      console.error('Error loading doctors:', data.message);
    }
  } catch (error) {
    console.error('Error:', error);
  }
}

function renderDoctorsList(doctors, filterDepartment = null) {
  const container = document.getElementById('doctors-container');

  if (!doctors || doctors.length === 0) {
    const departmentText = filterDepartment ? ` in ${filterDepartment}` : '';
    container.innerHTML = `
      <div class="empty-state">
        <i class='bx bxs-user-md'></i>
        <p>No available doctors${departmentText}</p>
      </div>
    `;
    return;
  }

  container.innerHTML = doctors
    .map(
      (doctor) => `
    <div class="doctor-item" onclick="selectDoctor(${doctor.id}, '${doctor.first_name} ${
        doctor.last_name
      }', '${doctor.department}')">
      <div class="doctor-info">
        <h4>${doctor.first_name} ${doctor.last_name}</h4>
        <span class="status-badge status-available">
          <i class='bx bxs-circle'></i> Available
        </span>
      </div>
      <div class="doctor-detail"><strong>Department:</strong> ${doctor.department}</div>
      <div class="doctor-detail"><strong>Phone:</strong> ${doctor.phone || 'N/A'}</div>
    </div>
  `
    )
    .join('');
}

async function loadDoctorAvailability(doctorId) {
  try {
    const response = await fetch(`./backend/php/get-doctor-availability.php?doctor_id=${doctorId}`);
    const data = await response.json();

    if (data.success) {
      const element = document.getElementById(`next-available-${doctorId}`);
      if (element) {
        element.textContent = formatTime(data.nextAvailable);
      }
    }
  } catch (error) {
    console.error('Error loading availability:', error);
  }
}

function selectDoctor(doctorId, doctorName, department) {
  document.getElementById('selected-doctor').value = `${doctorName} - ${department}`;
  document.getElementById('selected-doctor-id').value = doctorId;

  document.querySelectorAll('.doctor-item').forEach((item) => {
    item.classList.remove('selected');
  });
  event.target.closest('.doctor-item')?.classList.add('selected');

  document.getElementById('department-select').value = department;
}

async function loadPatients() {
  try {
    const response = await fetch('./backend/php/get-patients.php');
    const data = await response.json();

    if (data.success) {
      const select = document.getElementById('patient-select');
      const options = data.patients
        .map(
          (patient) =>
            `<option value="${patient.id}">${patient.first_name} ${patient.last_name}</option>`
        )
        .join('');
      select.innerHTML = '<option value="">-- Select a patient --</option>' + options;
    }
  } catch (error) {
    console.error('Error loading patients:', error);
  }
}

async function loadAppointments() {
  const tbody = document.getElementById('appointments-body');
  tbody.innerHTML =
    '<tr><td colspan="5" style="text-align:center;">Loading appointments...</td></tr>';

  try {
    const response = await fetch('./backend/php/get-appointments.php');
    const data = await response.json();

    if (data.success) {
      renderAppointmentsTable(data.appointments);
    } else {
      console.error('Error loading appointments:', data.message);
      tbody.innerHTML =
        '<tr><td colspan="5" style="text-align:center;color:#dc2626;">Error loading appointments</td></tr>';
    }
  } catch (error) {
    console.error('Error:', error);
    tbody.innerHTML =
      '<tr><td colspan="5" style="text-align:center;color:#dc2626;">Network error</td></tr>';
  }
}

function renderAppointmentsTable(appointments) {
  const tbody = document.getElementById('appointments-body');

  if (!appointments || appointments.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5">
          <div class="empty-state">
            <i class='bx bxs-calendar'></i>
            <p>No appointments scheduled</p>
          </div>
        </td>
      </tr>
    `;
    return;
  }

  tbody.innerHTML = appointments
    .map(
      (apt) => `
    <tr>
      <td>${apt.patient_name}</td>
      <td>${apt.doctor_name}</td>
      <td>${apt.department}</td>
      <td>${formatDateTime(apt.appointment_time)}</td>
      <td><span class="status-badge" style="background: rgba(59, 130, 246, 0.2); color: #3b82f6;">${
        apt.status
      }</span></td>
    </tr>
  `
    )
    .join('');
}

async function loadAllDoctors() {
  const container = document.getElementById('all-doctors-container');
  container.innerHTML = '<div class="loading"><div class="spinner"></div>Loading doctors...</div>';

  try {
    const response = await fetch('./backend/php/get-all-doctors.php');
    const data = await response.json();

    if (data.success) {
      renderAllDoctorsList(data.doctors);
    } else {
      container.innerHTML = `<div class="empty-state"><i class='bx bxs-error'></i><p>Error: ${data.message}</p></div>`;
    }
  } catch (error) {
    console.error('Error:', error);
    container.innerHTML = `<div class="empty-state"><i class='bx bxs-error'></i><p>Error loading doctors. Please try again.</p></div>`;
  }
}

function renderAllDoctorsList(doctors) {
  const container = document.getElementById('all-doctors-container');

  if (!doctors || doctors.length === 0) {
    container.innerHTML = `
      <div class="empty-state">
        <i class='bx bxs-user-md'></i>
        <p>No doctors found</p>
      </div>
    `;
    return;
  }

  container.innerHTML = doctors
    .map(
      (doctor) => `
    <div class="doctor-item">
      <div class="doctor-info">
        <h4>${doctor.first_name} ${doctor.last_name}</h4>
      </div>
      <div class="doctor-detail"><strong>Role:</strong> ${doctor.role}</div>
      <div class="doctor-detail"><strong>Department:</strong> ${doctor.department}</div>
      <div class="doctor-detail"><strong>Email:</strong> ${doctor.email || 'N/A'}</div>
      <div class="doctor-detail"><strong>Phone:</strong> ${doctor.phone || 'N/A'}</div>
      ${
        doctor.license_number
          ? `<div class="doctor-detail"><strong>License:</strong> ${doctor.license_number}</div>`
          : ''
      }
    </div>
  `
    )
    .join('');
}

document.getElementById('allocation-form').addEventListener('submit', async (e) => {
  e.preventDefault();

  const doctorId = document.getElementById('selected-doctor-id').value;
  const patientId = document.getElementById('patient-select').value;
  const appointmentTime = document.getElementById('appointment-time').value;
  const department = document.getElementById('department-select').value;
  const notes = document.getElementById('notes').value;

  if (!doctorId) {
    showMessage('Please select a doctor', 'error');
    return;
  }

  if (!patientId || !appointmentTime || !department) {
    showMessage('Please fill in all required fields', 'error');
    return;
  }

  try {
    const submitBtn = document.getElementById('submit-btn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Allocating...';

    // Use different endpoint if scheduling from a request
    const endpoint = currentRequestId
      ? './backend/php/schedule-from-request.php'
      : './backend/php/create-appointment.php';

    const requestData = currentRequestId
      ? {
          request_id: currentRequestId,
          staff_id: doctorId,
          appointment_time: appointmentTime,
          notes: notes,
        }
      : {
          patient_id: patientId,
          staff_id: doctorId,
          appointment_time: appointmentTime,
          department: department,
          notes: notes,
          status: 'Scheduled',
        };

    const response = await fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(requestData),
    });

    const data = await response.json();

    if (data.success) {
      showMessage('Appointment allocated successfully!', 'success');
      document.getElementById('allocation-form').reset();
      document.getElementById('selected-doctor').value = '';
      document.getElementById('selected-doctor-id').value = '';
      currentRequestId = null; // Reset request ID
      setTimeout(() => {
        loadAppointments();
        loadAppointmentRequests(); // Refresh requests
      }, 1500);
    } else {
      console.error('Server error:', data);
      showMessage(data.message || 'Error creating appointment', 'error');
    }
  } catch (error) {
    console.error('Error:', error);
    showMessage('Error creating appointment: ' + error.message, 'error');
  } finally {
    const submitBtn = document.getElementById('submit-btn');
    submitBtn.disabled = false;
    submitBtn.textContent = '✓ Allocate Appointment';
  }
});

function showMessage(text, type) {
  const messageEl = document.getElementById('allocation-message');
  messageEl.textContent = text;
  messageEl.className = `message ${type}`;
  messageEl.style.display = 'block';

  setTimeout(() => {
    messageEl.style.display = 'none';
  }, 4000);
}

function formatTime(timeString) {
  if (!timeString) return 'N/A';
  const date = new Date(timeString);
  return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
}

function formatDateTime(dateString) {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

function logout() {
  localStorage.removeItem('secretary');
  window.location.href = 'role-selection.html';
}

window.addEventListener('DOMContentLoaded', () => {
  loadAppointmentRequests();
  loadAvailableDoctors();
  loadPatients();
});
