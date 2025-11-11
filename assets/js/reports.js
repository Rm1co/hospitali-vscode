function renderReports(){
  const container = document.getElementById('reportsArea');
  if(!container) return;
  container.innerHTML = '<div class="placeholder">Reports and charts will appear here (connect to backend to populate).</div>';
}
document.addEventListener('DOMContentLoaded', renderReports);
