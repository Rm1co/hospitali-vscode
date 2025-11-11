const patients = [
  {id: 1, name: 'Olivia Martin', gender: 'F', age: 29, department: 'Cardiology'},
  {id: 2, name: 'James Carter', gender: 'M', age: 42, department: 'ER'},
  {id: 3, name: 'Maria Gonzales', gender: 'F', age: 34, department: 'Gynecology'}
];

function renderPatients() {
  const table = `
    <table>
      <thead><tr><th>ID</th><th>Name</th><th>Gender</th><th>Age</th><th>Department</th></tr></thead>
      <tbody>
        ${patients.map(p => `<tr><td>${p.id}</td><td>${p.name}</td><td>${p.gender}</td><td>${p.age}</td><td>${p.department}</td></tr>`).join('')}
      </tbody>
    </table>`;
  const container = document.getElementById('patientsTable');
  if(container) container.innerHTML = table;
}

function openPatientForm(){ alert('Open patient form (placeholder)'); }

document.addEventListener('DOMContentLoaded', renderPatients);
