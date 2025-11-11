// Demo data (used by both index and appointments page)
window._demoAppointments = [
    {time:'08:30 AM', patient:'Olivia Martin', doctor:'Dr. Michael Rutto', dept:'Cardiology', status:'Confirmed'},
    {time:'09:00 AM', patient:'Robert Brown', doctor:'Dr. Joyce Mbiriri', dept:'ER', status:'Walk-in'},
    {time:'10:15 AM', patient:'Sophia Lee', doctor:'Dr. Jason Mageto', dept:'Pediatrics', status:'Confirmed'},
    {time:'11:00 AM', patient:'Michael Nguyen', doctor:'Dr. Wilson Mutua', dept:'Cardiology', status:'Cancelled'},
    {time:'01:30 PM', patient:'Emma Thomas', doctor:'Dr. Brian Mathara', dept:'ER', status:'Confirmed'}
];

function renderAppointmentsTable(){
    const container = document.getElementById('appointmentsTable');
    if(!container) return;
    const table = `<table><thead><tr><th>Time</th><th>Patient</th><th>Doctor</th><th>Department</th><th>Status</th></tr></thead><tbody>${
        window._demoAppointments.map(a=>`<tr><td>${a.time}</td><td>${a.patient}</td><td>${a.doctor}</td><td>${a.dept}</td><td><span class="pill ${a.status==='Confirmed'?'success':a.status==='Walk-in'?'info':'warn'}">${a.status}</span></td></tr>`).join('')
    }</tbody></table>`;
    container.innerHTML = table;
}

function addAppointment(){ alert('Open appointment form (placeholder)'); }

document.addEventListener('DOMContentLoaded', renderAppointmentsTable);
