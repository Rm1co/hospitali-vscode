async function renderReports() {
  const container = document.getElementById('reportsArea');
  if (!container) return;

  const admin = JSON.parse(localStorage.getItem('admin') || 'null');
  const permissions = admin?.permissions || {};

  // If Finance Admin, show financial reports
  if (permissions.manage_billing) {
    await renderFinancialReports(container);
  }
  // Lab Admin sees lab reports
  else if (permissions.manage_lab || permissions.view_all_lab_reports) {
    await renderLabReports(container);
  }
  // Otherwise show general reports
  else {
    container.innerHTML =
      '<div class="placeholder">Reports and charts will appear here (connect to backend to populate).</div>';
  }
}

async function renderFinancialReports(container) {
  container.innerHTML = '<p>Loading financial reports...</p>';

  try {
    const response = await fetch('../../backend/php/billing.php?action=list');
    const result = await response.json();

    if (result.success) {
      const invoices = result.data || [];

      // Calculate financial statistics (case-insensitive status check)
      const totalInvoices = invoices.length;
      const paidInvoices = invoices.filter((inv) => inv.status.toLowerCase() === 'paid');
      const unpaidInvoices = invoices.filter((inv) => inv.status.toLowerCase() === 'unpaid');
      const pendingInvoices = invoices.filter((inv) => inv.status.toLowerCase() === 'pending');

      const totalRevenue = paidInvoices.reduce((sum, inv) => sum + parseFloat(inv.total), 0);
      const totalOutstanding = unpaidInvoices.reduce((sum, inv) => sum + parseFloat(inv.total), 0);
      const totalPending = pendingInvoices.reduce((sum, inv) => sum + parseFloat(inv.total), 0);

      // Sort invoices by date (most recent first)
      const recentInvoices = [...invoices]
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        .slice(0, 10);

      container.innerHTML = `
        <div style="padding: 20px;">
          <h3 style="margin-bottom: 24px; color: #1f2937;">Financial Reports & Analytics</h3>
          
          <!-- Summary Cards -->
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px;">
            <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
              <div style="color: #6b7280; font-size: 14px; margin-bottom: 8px;">Total Invoices</div>
              <div style="font-size: 32px; font-weight: 700; color: #1f2937;">${totalInvoices}</div>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
              <div style="color: #6b7280; font-size: 14px; margin-bottom: 8px;">Total Revenue (Paid)</div>
              <div style="font-size: 32px; font-weight: 700; color: #10b981;">$${totalRevenue.toFixed(
                2
              )}</div>
              <div style="color: #6b7280; font-size: 12px; margin-top: 4px;">${
                paidInvoices.length
              } paid invoices</div>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
              <div style="color: #6b7280; font-size: 14px; margin-bottom: 8px;">Outstanding (Unpaid)</div>
              <div style="font-size: 32px; font-weight: 700; color: #ef4444;">$${totalOutstanding.toFixed(
                2
              )}</div>
              <div style="color: #6b7280; font-size: 12px; margin-top: 4px;">${
                unpaidInvoices.length
              } unpaid invoices</div>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
              <div style="color: #6b7280; font-size: 14px; margin-bottom: 8px;">Pending</div>
              <div style="font-size: 32px; font-weight: 700; color: #f59e0b;">$${totalPending.toFixed(
                2
              )}</div>
              <div style="color: #6b7280; font-size: 12px; margin-top: 4px;">${
                pendingInvoices.length
              } pending invoices</div>
            </div>
          </div>

          <!-- Status Breakdown -->
          <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e5e7eb; margin-bottom: 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h4 style="margin-top: 0; margin-bottom: 16px; color: #1f2937;">Invoice Status Breakdown</h4>
            <div style="display: flex; gap: 24px; flex-wrap: wrap;">
              <div style="flex: 1; min-width: 200px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                  <span style="color: #6b7280;">Paid</span>
                  <span style="font-weight: 600; color: #10b981;">${paidInvoices.length} (${(
        (paidInvoices.length / totalInvoices) *
        100
      ).toFixed(1)}%)</span>
                </div>
                <div style="height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">
                  <div style="height: 100%; background: #10b981; width: ${
                    (paidInvoices.length / totalInvoices) * 100
                  }%;"></div>
                </div>
              </div>
              
              <div style="flex: 1; min-width: 200px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                  <span style="color: #6b7280;">Unpaid</span>
                  <span style="font-weight: 600; color: #ef4444;">${unpaidInvoices.length} (${(
        (unpaidInvoices.length / totalInvoices) *
        100
      ).toFixed(1)}%)</span>
                </div>
                <div style="height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">
                  <div style="height: 100%; background: #ef4444; width: ${
                    (unpaidInvoices.length / totalInvoices) * 100
                  }%;"></div>
                </div>
              </div>
              
              <div style="flex: 1; min-width: 200px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                  <span style="color: #6b7280;">Pending</span>
                  <span style="font-weight: 600; color: #f59e0b;">${pendingInvoices.length} (${(
        (pendingInvoices.length / totalInvoices) *
        100
      ).toFixed(1)}%)</span>
                </div>
                <div style="height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">
                  <div style="height: 100%; background: #f59e0b; width: ${
                    (pendingInvoices.length / totalInvoices) * 100
                  }%;"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Payment Transactions -->
          ${
            paidInvoices.length > 0
              ? `
          <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 32px;">
            <h4 style="margin-top: 0; margin-bottom: 16px; color: #1f2937;">Recent Payments</h4>
            <div style="overflow-x: auto;">
              <table style="width: 100%; border-collapse: collapse;">
                <thead>
                  <tr style="border-bottom: 2px solid #e5e7eb; text-align: left;">
                    <th style="padding: 12px 8px; color: #6b7280; font-weight: 600; font-size: 14px;">Invoice ID</th>
                    <th style="padding: 12px 8px; color: #6b7280; font-weight: 600; font-size: 14px;">Patient</th>
                    <th style="padding: 12px 8px; color: #6b7280; font-weight: 600; font-size: 14px;">Amount</th>
                    <th style="padding: 12px 8px; color: #6b7280; font-weight: 600; font-size: 14px;">Payment Method</th>
                    <th style="padding: 12px 8px; color: #6b7280; font-weight: 600; font-size: 14px;">Transaction ID</th>
                    <th style="padding: 12px 8px; color: #6b7280; font-weight: 600; font-size: 14px;">Date</th>
                  </tr>
                </thead>
                <tbody>
                  ${paidInvoices
                    .slice(0, 10)
                    .map(
                      (inv) => `
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                      <td style="padding: 12px 8px; color: #1f2937; font-weight: 600;">#${
                        inv.id
                      }</td>
                      <td style="padding: 12px 8px; color: #1f2937;">${
                        inv.patient_name || 'N/A'
                      }</td>
                      <td style="padding: 12px 8px; color: #10b981; font-weight: 700;">$${parseFloat(
                        inv.total
                      ).toFixed(2)}</td>
                      <td style="padding: 12px 8px; color: #1f2937;">
                        ${
                          inv.payment_method
                            ? `<span style="padding: 4px 8px; background: #f3f4f6; border-radius: 4px; font-size: 12px;">${
                                inv.payment_method.charAt(0).toUpperCase() +
                                inv.payment_method.slice(1)
                              }</span>`
                            : '-'
                        }
                      </td>
                      <td style="padding: 12px 8px; color: #6b7280; font-size: 12px; font-family: monospace;">${
                        inv.transaction_id || '-'
                      }</td>
                      <td style="padding: 12px 8px; color: #6b7280; font-size: 14px;">${
                        inv.payment_date
                          ? new Date(inv.payment_date).toLocaleDateString()
                          : new Date(inv.created_at).toLocaleDateString()
                      }</td>
                    </tr>
                  `
                    )
                    .join('')}
                </tbody>
              </table>
            </div>
          </div>
          `
              : ''
          }

          <!-- Recent Invoices -->
          <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h4 style="margin-top: 0; margin-bottom: 16px; color: #1f2937;">Recent Invoices (Last 10)</h4>
            <div style="overflow-x: auto;">
              <table style="width: 100%; border-collapse: collapse;">
                <thead>
                  <tr style="border-bottom: 2px solid #e5e7eb; text-align: left;">
                    <th style="padding: 12px 8px; color: #6b7280; font-weight: 600; font-size: 14px;">Invoice ID</th>
                    <th style="padding: 12px 8px; color: #6b7280; font-weight: 600; font-size: 14px;">Patient</th>
                    <th style="padding: 12px 8px; color: #6b7280; font-weight: 600; font-size: 14px;">Amount</th>
                    <th style="padding: 12px 8px; color: #6b7280; font-weight: 600; font-size: 14px;">Status</th>
                    <th style="padding: 12px 8px; color: #6b7280; font-weight: 600; font-size: 14px;">Date</th>
                  </tr>
                </thead>
                <tbody>
                  ${recentInvoices
                    .map(
                      (inv) => `
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                      <td style="padding: 12px 8px; color: #1f2937;">#${inv.id}</td>
                      <td style="padding: 12px 8px; color: #1f2937;">${
                        inv.patient_name || 'N/A'
                      }</td>
                      <td style="padding: 12px 8px; color: #1f2937; font-weight: 600;">$${parseFloat(
                        inv.total
                      ).toFixed(2)}</td>
                      <td style="padding: 12px 8px;">
                        <span style="padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; background: ${
                          inv.status.toLowerCase() === 'paid'
                            ? '#d1fae5'
                            : inv.status.toLowerCase() === 'unpaid'
                            ? '#fee2e2'
                            : '#fef3c7'
                        }; color: ${
                        inv.status.toLowerCase() === 'paid'
                          ? '#065f46'
                          : inv.status.toLowerCase() === 'unpaid'
                          ? '#991b1b'
                          : '#92400e'
                      };">
                          ${inv.status.charAt(0).toUpperCase() + inv.status.slice(1)}
                        </span>
                      </td>
                      <td style="padding: 12px 8px; color: #6b7280; font-size: 14px;">${new Date(
                        inv.created_at
                      ).toLocaleDateString()}</td>
                    </tr>
                  `
                    )
                    .join('')}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      `;
    } else {
      container.innerHTML =
        '<p style="color: red; padding: 20px;">Error loading financial data.</p>';
    }
  } catch (error) {
    console.error('Failed to load financial reports:', error);
    container.innerHTML =
      '<p style="color: red; padding: 20px;">Failed to load financial reports. Please try again.</p>';
  }
}
function setupAdminNavigation() {
  const admin = JSON.parse(localStorage.getItem('admin') || 'null');
  if (!admin) return;

  const permissions = admin.permissions || {};
  const mainNav = document.getElementById('main-nav');
  const pageTitle = document.getElementById('page-title');
  if (!mainNav) return;

  const navLinks = [];

  // Finance Admin sees Financial Reports
  if (permissions.manage_billing) {
    navLinks.push('<li><a href="billing.html">Billing & Invoices</a></li>');
    navLinks.push('<li><a href="reports.html" class="active">Financial Reports</a></li>');
    if (pageTitle) pageTitle.textContent = 'Financial Reports';
  }
  // Lab Admin sees full lab management
  else if (permissions.manage_lab || permissions.view_all_lab_reports) {
    navLinks.push('<li><a href="lab-admin-dashboard.html">Lab Management</a></li>');
    navLinks.push('<li><a href="reports.html" class="active">Lab Reports</a></li>');
    if (pageTitle) pageTitle.textContent = 'Lab Reports';
  }

  navLinks.push('<li><a href="#" onclick="logout()">Logout</a></li>');
  mainNav.innerHTML = '<ul>' + navLinks.join('') + '</ul>';
}

async function renderLabReports(container) {
  container.innerHTML = '<p>Loading lab reports...</p>';

  try {
    const response = await fetch('../../backend/php/lab-tests.php?action=list');
    const result = await response.json();

    if (result.success) {
      const tests = result.data || [];

      // Calculate lab statistics
      const totalTests = tests.length;
      const approvedTests = tests.filter((t) => t.status === 'approved');
      const pendingTests = tests.filter((t) => t.status === 'pending');
      const rejectedTests = tests.filter((t) => t.status === 'rejected');

      // Count by test type
      const testTypes = {};
      tests.forEach((test) => {
        testTypes[test.test_type] = (testTypes[test.test_type] || 0) + 1;
      });

      // Sort tests by date (most recent first) - handle both date strings and timestamps
      const recentTests = [...tests]
        .sort((a, b) => {
          const dateA = new Date(a.submitted_at);
          const dateB = new Date(b.submitted_at);
          return dateB - dateA; // Latest first
        })
        .slice(0, 15);

      container.innerHTML = `
        <div style="padding: 20px;">
          <h3 style="margin-bottom: 24px; color: #1f2937;">Laboratory Reports & Analytics</h3>
          
          <!-- Summary Cards -->
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px;">
            <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
              <div style="color: #6b7280; font-size: 14px; margin-bottom: 8px;">Total Tests</div>
              <div style="font-size: 32px; font-weight: 700; color: #1f2937;">${totalTests}</div>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
              <div style="color: #6b7280; font-size: 14px; margin-bottom: 8px;">Approved</div>
              <div style="font-size: 32px; font-weight: 700; color: #10b981;">${
                approvedTests.length
              }</div>
              <div style="color: #6b7280; font-size: 12px; margin-top: 4px;">${(
                (approvedTests.length / totalTests) *
                100
              ).toFixed(1)}% of total</div>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
              <div style="color: #6b7280; font-size: 14px; margin-bottom: 8px;">Pending Review</div>
              <div style="font-size: 32px; font-weight: 700; color: #f59e0b;">${
                pendingTests.length
              }</div>
              <div style="color: #6b7280; font-size: 12px; margin-top: 4px;">Awaiting approval</div>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
              <div style="color: #6b7280; font-size: 14px; margin-bottom: 8px;">Rejected</div>
              <div style="font-size: 32px; font-weight: 700; color: #ef4444;">${
                rejectedTests.length
              }</div>
              <div style="color: #6b7280; font-size: 12px; margin-top: 4px;">${(
                (rejectedTests.length / totalTests) *
                100
              ).toFixed(1)}% of total</div>
            </div>
          </div>

          <!-- Test Type Breakdown -->
          <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e5e7eb; margin-bottom: 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h4 style="margin-top: 0; margin-bottom: 16px; color: #1f2937;">Tests by Type</h4>
            <div style="display: grid; gap: 12px;">
              ${Object.entries(testTypes)
                .map(
                  ([type, count]) => `
                <div>
                  <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: #6b7280;">${type}</span>
                    <span style="font-weight: 600; color: #1f2937;">${count} (${(
                    (count / totalTests) *
                    100
                  ).toFixed(1)}%)</span>
                  </div>
                  <div style="height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">
                    <div style="height: 100%; background: #3b82f6; width: ${
                      (count / totalTests) * 100
                    }%;"></div>
                  </div>
                </div>
              `
                )
                .join('')}
            </div>
          </div>

          <!-- Recent Lab Tests -->
          <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h4 style="margin-top: 0; margin-bottom: 16px; color: #1f2937;">Recent Lab Tests (Last 15)</h4>
            <div style="overflow-x: auto;">
              <table style="width: 100%; border-collapse: collapse;">
                <thead>
                  <tr style="border-bottom: 2px solid #e5e7eb; text-align: left;">
                    <th style="padding: 12px 8px; color: #6b7280; font-weight: 600; font-size: 14px;">Test ID</th>
                    <th style="padding: 12px 8px; color: #6b7280; font-weight: 600; font-size: 14px;">Patient</th>
                    <th style="padding: 12px 8px; color: #6b7280; font-weight: 600; font-size: 14px;">Test Type</th>
                    <th style="padding: 12px 8px; color: #6b7280; font-weight: 600; font-size: 14px;">Technician</th>
                    <th style="padding: 12px 8px; color: #6b7280; font-weight: 600; font-size: 14px;">Status</th>
                    <th style="padding: 12px 8px; color: #6b7280; font-weight: 600; font-size: 14px;">Date</th>
                  </tr>
                </thead>
                <tbody>
                  ${recentTests
                    .map(
                      (test) => `
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                      <td style="padding: 12px 8px; color: #1f2937;">#${test.id}</td>
                      <td style="padding: 12px 8px; color: #1f2937;">${test.patient_name}</td>
                      <td style="padding: 12px 8px; color: #1f2937;">${test.test_type}</td>
                      <td style="padding: 12px 8px; color: #6b7280; font-size: 14px;">${
                        test.technician_name
                      }</td>
                      <td style="padding: 12px 8px;">
                        <span style="padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; background: ${
                          test.status === 'approved'
                            ? '#d1fae5'
                            : test.status === 'rejected'
                            ? '#fee2e2'
                            : '#fef3c7'
                        }; color: ${
                        test.status === 'approved'
                          ? '#065f46'
                          : test.status === 'rejected'
                          ? '#991b1b'
                          : '#92400e'
                      };">
                          ${test.status.toUpperCase()}
                        </span>
                      </td>
                      <td style="padding: 12px 8px; color: #6b7280; font-size: 14px;">${new Date(
                        test.submitted_at
                      ).toLocaleDateString()}</td>
                    </tr>
                  `
                    )
                    .join('')}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      `;
    } else {
      container.innerHTML = '<p style="color: red; padding: 20px;">Error loading lab reports.</p>';
    }
  } catch (error) {
    console.error('Failed to load lab reports:', error);
    container.innerHTML =
      '<p style="color: red; padding: 20px;">Failed to load lab reports. Please try again.</p>';
  }
}

function logout() {
  localStorage.removeItem('admin');
  window.location.href = '../admin/admin-login.html';
}

document.addEventListener('DOMContentLoaded', () => {
  setupAdminNavigation();
  renderReports();

  // Auto-refresh reports every 30 seconds to show updated payment data
  setInterval(() => {
    renderReports();
  }, 30000);
});
