let inventory = [];

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

  // Add animation
  const style = document.createElement('style');
  style.textContent = `
    @keyframes slideIn {
      from {
        transform: translateX(400px);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
    @keyframes slideOut {
      from {
        transform: translateX(0);
        opacity: 1;
      }
      to {
        transform: translateX(400px);
        opacity: 0;
      }
    }
  `;
  if (!document.querySelector('style[data-success-msg]')) {
    style.setAttribute('data-success-msg', 'true');
    document.head.appendChild(style);
  }

  document.body.appendChild(msg);

  // Auto-remove after 3 seconds
  setTimeout(() => {
    msg.style.animation = 'slideOut 0.3s ease';
    setTimeout(() => msg.remove(), 300);
  }, 3000);
}

async function loadInventory() {
  try {
    const response = await fetch('backend/php/inventory.php?action=list');
    const result = await response.json();
    if (result.success) {
      inventory = result.data || [];
      // Sort by ID in ascending order (1, 2, 3...)
      inventory.sort((a, b) => a.id - b.id);
      renderInventory();
    } else {
      alert('Error loading inventory: ' + result.error);
    }
  } catch (error) {
    console.error('Failed to load inventory:', error);
    alert('Failed to load inventory');
  }
}

function renderInventory() {
  const container = document.getElementById('inventoryTable');
  if (!container) return;

  if (inventory.length === 0) {
    container.innerHTML = '<p style="text-align:center;color:#666;">No inventory items found</p>';
    return;
  }

  const table = `<table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Item</th>
        <th>Quantity</th>
        <th>Unit</th>
        <th>Added</th>
      </tr>
    </thead>
    <tbody>
      ${inventory
        .map(
          (i) => `
        <tr>
          <td>${i.id}</td>
          <td>${i.name}</td>
          <td>${i.quantity}</td>
          <td>${i.unit}</td>
          <td>${new Date(i.created_at).toLocaleDateString()}</td>
        </tr>
      `
        )
        .join('')}
    </tbody>
  </table>`;
  container.innerHTML = table;
}

function addItem() {
  const container = document.getElementById('inventoryTable');
  if (!container) return;

  // Hide the inventory list while the form is visible
  container.style.display = 'none';

  const formWrap = document.createElement('div');
  formWrap.id = 'inventory-add-form-wrap';
  formWrap.innerHTML = `
    <form style="max-width:700px;margin:20px auto;">
      <div style="display:flex;flex-direction:column;gap:12px;padding:20px;background:white;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
        <h3 style="margin:0;margin-bottom:10px;">Add Inventory Item</h3>

        <label>
          <span style="display:block;margin-bottom:4px;font-weight:500;">Item Name *</span>
          <input type="text" name="name" required placeholder="e.g., Paracetamol 500mg" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;">
        </label>

        <label>
          <span style="display:block;margin-bottom:4px;font-weight:500;">Quantity *</span>
          <input type="number" name="quantity" required min="1" placeholder="e.g., 100" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;">
        </label>

        <label>
          <span style="display:block;margin-bottom:4px;font-weight:500;">Unit *</span>
          <input type="text" name="unit" required placeholder="e.g., tabs, bags, pcs" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;">
        </label>

        <div style="display:flex;gap:10px;margin-top:10px;">
          <button type="submit" class="btn" style="flex:1;">Add Item</button>
          <button type="button" id="inventory-add-cancel" class="btn" style="flex:1;background:#999;">Cancel</button>
        </div>
      </div>
    </form>
  `;

  // Insert the form wrapper right where the table was
  container.parentNode.insertBefore(formWrap, container);

  const form = formWrap.querySelector('form');
  const cancelBtn = document.getElementById('inventory-add-cancel');

  const cleanup = () => {
    const wrap = document.getElementById('inventory-add-form-wrap');
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
      name: formData.get('name'),
      quantity: parseInt(formData.get('quantity')),
      unit: formData.get('unit'),
    };

    try {
      const response = await fetch('backend/php/inventory.php?action=add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
      });

      const result = await response.json();
      if (result.success) {
        // remove form, restore list, and reload
        cleanup();
        showSuccessMessage('Item added successfully!');
        await loadInventory();
      } else {
        alert('Error: ' + result.error);
      }
    } catch (error) {
      console.error('Failed to add item:', error);
      alert('Failed to add item');
    }
  };
}

document.addEventListener('DOMContentLoaded', loadInventory);
