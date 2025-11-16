// Check admin authentication and setup navigation
function setupAdminNavigation() {
  const admin = JSON.parse(localStorage.getItem('admin') || 'null');
  if (!admin) {
    window.location.href = '../admin/admin-login.html';
    return;
  }

  const permissions = admin.permissions || {};
  const navMenu = document.getElementById('nav-menu');
  if (!navMenu) return;

  const navLinks = [];

  // Show links based on permissions - same as Staff Management page
  if (permissions.manage_staff) {
    navLinks.push('<li><a href="admin-panel.html">Staff Management</a></li>');
    navLinks.push('<li><a href="staff.html" class="active">View All Staff</a></li>');
  }

  // Logout link
  navLinks.push('<li><a href="#" onclick="logout()">Logout</a></li>');

  navMenu.innerHTML = navLinks.join('');
}

async function renderStaff() {
  const container = document.getElementById('staffTable');
  if (!container) return;

  container.innerHTML = '<p>Loading staff...</p>';

  try {
    const response = await fetch('./backend/php/admin-get-staff.php');
    const data = await response.json();

    if (data.success && data.staff && data.staff.length > 0) {
      // Sort staff by ID in ascending order (1, 2, 3...)
      const sortedStaff = data.staff.sort((a, b) => a.id - b.id);

      const table = `<table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Department</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>${sortedStaff
          .map(
            (s) => `
          <tr>
            <td>${s.id}</td>
            <td>${s.first_name} ${s.last_name}</td>
            <td>${s.email}</td>
            <td>${s.role}</td>
            <td>${s.department}</td>
            <td><span class="status-badge ${s.is_activated ? 'status-active' : 'status-pending'}">${
              s.is_activated ? 'Active' : 'Pending'
            }</span></td>
          </tr>`
          )
          .join('')}
        </tbody>
      </table>`;
      container.innerHTML = table;
    } else {
      container.innerHTML =
        '<p>No staff members found. Add staff from the Staff Management tab.</p>';
    }
  } catch (error) {
    console.error('Error loading staff:', error);
    container.innerHTML = '<p style="color: red;">Error loading staff list. Please try again.</p>';
  }
}

function addStaff() {
  alert('Open add staff form (placeholder)');
}

function logout() {
  localStorage.removeItem('admin');
  window.location.href = '../admin/admin-login.html';
}

document.addEventListener('DOMContentLoaded', () => {
  setupAdminNavigation();
  renderStaff();
});
