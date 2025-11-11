const inventory = [
  {id:1, name:'Paracetamol 500mg', stock:120, unit:'tabs'},
  {id:2, name:'Saline 500ml', stock:25, unit:'bags'},
  {id:3, name:'Syringes', stock:340, unit:'pcs'}
];

function renderInventory(){
  const container = document.getElementById('inventoryTable');
  if(!container) return;
  const table = `<table><thead><tr><th>ID</th><th>Item</th><th>Stock</th><th>Unit</th></tr></thead><tbody>${
    inventory.map(i=>`<tr><td>${i.id}</td><td>${i.name}</td><td>${i.stock}</td><td>${i.unit}</td></tr>`).join('')
  }</tbody></table>`;
  container.innerHTML = table;
}

function addItem(){ alert('Open add inventory item (placeholder)'); }

document.addEventListener('DOMContentLoaded', renderInventory);
