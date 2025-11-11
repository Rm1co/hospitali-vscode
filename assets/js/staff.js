const staff = [
  {id:1, name:'Dr. Michael Rutto', role:'Cardiologist', status:'On duty'},
  {id:2, name:'Joyce Mbiriri', role:'Nurse', status:'On duty'},
  {id:3, name:'Dr. Wilson Mutua', role:'ER', status:'On call'}
];

function renderStaff(){
  const container = document.getElementById('staffTable');
  if(!container) return;
  const table = `<table><thead><tr><th>ID</th><th>Name</th><th>Role</th><th>Status</th></tr></thead><tbody>${
    staff.map(s=>`<tr><td>${s.id}</td><td>${s.name}</td><td>${s.role}</td><td>${s.status}</td></tr>`).join('')
  }</tbody></table>`;
  container.innerHTML = table;
}

function addStaff(){ alert('Open add staff form (placeholder)'); }

document.addEventListener('DOMContentLoaded', renderStaff);
