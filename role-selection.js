// Theme toggle
const themeToggle = document.getElementById('theme-toggle');
const html = document.documentElement;

// Load saved theme preference
const savedTheme = localStorage.getItem('theme') || 'light';
html.setAttribute('data-theme', savedTheme);
updateThemeButton();

themeToggle.addEventListener('click', function() {
  const currentTheme = html.getAttribute('data-theme');
  const newTheme = currentTheme === 'light' ? 'dark' : 'light';
  
  html.setAttribute('data-theme', newTheme);
  localStorage.setItem('theme', newTheme);
  updateThemeButton();
});

function updateThemeButton() {
  const currentTheme = html.getAttribute('data-theme');
  themeToggle.textContent = currentTheme === 'light' ? '🌙 Dark Mode' : '☀️ Light Mode';
}

// Navigate to selected role login
function navigateTo(page) {
  window.location.href = page;
}

// Add keyboard support for role selection
document.addEventListener('keydown', function(event) {
  const cards = document.querySelectorAll('.role-card');
  if (event.key === 'ArrowRight') {
    const activeCard = document.activeElement;
    const currentIndex = Array.from(cards).indexOf(activeCard);
    if (currentIndex < cards.length - 1) {
      cards[currentIndex + 1].focus();
    }
  } else if (event.key === 'ArrowLeft') {
    const activeCard = document.activeElement;
    const currentIndex = Array.from(cards).indexOf(activeCard);
    if (currentIndex > 0) {
      cards[currentIndex - 1].focus();
    }
  } else if (event.key === 'Enter') {
    const activeCard = document.activeElement;
    if (cards.includes(activeCard)) {
      activeCard.click();
    }
  }
});

// Make cards keyboard accessible
document.querySelectorAll('.role-card').forEach(card => {
  card.setAttribute('tabindex', '0');
  card.setAttribute('role', 'button');
});
