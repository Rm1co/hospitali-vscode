const invoices = [
  {id:1001, patient:'Olivia Martin', amount: 340.50, status:'Unpaid'},
  {id:1002, patient:'James Carter', amount: 120.00, status:'Paid'}
];

function renderBilling(){
  const container = document.getElementById('billingTable');
  if(!container) return;
  const table = `<table><thead><tr><th>Invoice</th><th>Patient</th><th>Amount</th><th>Status</th></tr></thead><tbody>${
    invoices.map(inv=>`<tr><td>${inv.id}</td><td>${inv.patient}</td><td>${inv.amount.toFixed(2)}</td><td>${inv.status}</td></tr>`).join('')
  }</tbody></table>`;
  container.innerHTML = table;
}

function createInvoice(){ alert('Open invoice creation (placeholder)'); }

document.addEventListener('DOMContentLoaded', renderBilling);
