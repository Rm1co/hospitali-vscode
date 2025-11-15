let invoices = [];
let patients = [];

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

async function loadInvoices() {
  try {
    const response = await fetch('backend/php/billing.php?action=list');
    const result = await response.json();
    if (result.success) {
      invoices = result.data || [];
      invoices.sort((a, b) => a.id - b.id);
      renderBilling();
    } else {
      alert('Error loading invoices: ' + result.error);
    }
  } catch (error) {
    console.error('Failed to load invoices:', error);
    alert('Failed to load invoices');
  }
}

async function loadPatients() {
  try {
    const response = await fetch('backend/php/billing.php?action=patients');
    const result = await response.json();
    if (result.success) {
      patients = result.data || [];
    }
  } catch (error) {
    console.error('Failed to load patients:', error);
  }
}

function renderBilling() {
  const container = document.getElementById('billingTable');
  if (!container) return;

  if (invoices.length === 0) {
    container.innerHTML = '<p style="text-align:center;color:#666;">No invoices found</p>';
    return;
  }

  const table = `<table>
    <thead>
      <tr>
        <th>Invoice ID</th>
        <th>Patient</th>
        <th>Amount</th>
        <th>Status</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      ${invoices
        .map(
          (inv) => `
        <tr>
          <td>${inv.id}</td>
          <td>${inv.patient_name || 'N/A'}</td>
          <td>$${parseFloat(inv.total).toFixed(2)}</td>
          <td><span style="padding:4px 8px;border-radius:4px;background:${
            inv.status === 'Paid' ? '#d4edda' : '#fff3cd'
          };color:${inv.status === 'Paid' ? '#155724' : '#856404'}">${inv.status}</span></td>
          <td>${new Date(inv.created_at).toLocaleDateString()}</td>
        </tr>
      `
        )
        .join('')}
    </tbody>
  </table>`;
  container.innerHTML = table;
}

async function createInvoice() {
  const container = document.getElementById('billingTable');
  if (!container) return;

  // Load patients if not already loaded
  if (patients.length === 0) {
    await loadPatients();
  }

  container.style.display = 'none';

  const formWrap = document.createElement('div');
  formWrap.id = 'invoice-create-form-wrap';
  formWrap.innerHTML = `
    <form style="max-width:700px;margin:20px auto;">
      <div style="display:flex;flex-direction:column;gap:12px;padding:20px;background:white;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
        <h3 style="margin:0;margin-bottom:10px;">Create Invoice</h3>

        <label>
          <span style="display:block;margin-bottom:4px;font-weight:500;">Patient *</span>
          <select name="patient_id" required style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;">
            <option value="">-- Select Patient --</option>
            ${patients
              .map((p) => `<option value="${p.id}">${p.first_name} ${p.last_name}</option>`)
              .join('')}
          </select>
        </label>

        <label>
          <span style="display:block;margin-bottom:4px;font-weight:500;">Amount ($) *</span>
          <input type="number" name="total" required min="0" step="0.01" placeholder="e.g., 150.00" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;">
        </label>

        <label>
          <span style="display:block;margin-bottom:4px;font-weight:500;">Status *</span>
          <select name="status" required style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;">
            <option value="Unpaid">Unpaid</option>
            <option value="Paid">Paid</option>
            <option value="Pending">Pending</option>
          </select>
        </label>

        <div style="display:flex;gap:10px;margin-top:10px;">
          <button type="submit" class="btn" style="flex:1;">Create Invoice</button>
          <button type="button" id="invoice-create-cancel" class="btn" style="flex:1;background:#999;">Cancel</button>
        </div>
      </div>
    </form>
  `;

  container.parentNode.insertBefore(formWrap, container);

  const form = formWrap.querySelector('form');
  const cancelBtn = document.getElementById('invoice-create-cancel');

  const cleanup = () => {
    const wrap = document.getElementById('invoice-create-form-wrap');
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
      patient_id: parseInt(formData.get('patient_id')),
      total: parseFloat(formData.get('total')),
      status: formData.get('status'),
    };

    try {
      const response = await fetch('backend/php/billing.php?action=create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
      });

      const result = await response.json();
      if (result.success) {
        cleanup();
        showSuccessMessage('Invoice created successfully!');
        await loadInvoices();
      } else {
        alert('Error: ' + result.error);
      }
    } catch (error) {
      console.error('Failed to create invoice:', error);
      alert('Failed to create invoice');
    }
  };
}

document.addEventListener('DOMContentLoaded', () => {
  loadInvoices();
  loadPatients();
});
