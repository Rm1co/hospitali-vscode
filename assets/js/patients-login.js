const form = document.getElementById("login-form");
const errorMsg = document.getElementById("error-msg");
const themeToggle = document.getElementById("theme-toggle");
const root = document.documentElement;

// 🌙 Dark Mode
themeToggle.addEventListener("click", () => {
  const current = root.getAttribute("data-theme");
  const newTheme = current === "light" ? "dark" : "light";
  root.setAttribute("data-theme", newTheme);
  themeToggle.textContent = newTheme === "dark" ? "☀️ Light" : "🌙 Dark Mode";
  localStorage.setItem("theme", newTheme);
});

// Load saved theme
const savedTheme = localStorage.getItem("theme");
if (savedTheme) {
  root.setAttribute("data-theme", savedTheme);
  themeToggle.textContent = savedTheme === "dark" ? "☀️ Light" : "🌙 Dark Mode";
}

// 🔑 Simulated Login
form.addEventListener("submit", (e) => {
  e.preventDefault();
  const email = document.getElementById("email").value;
  const password = document.getElementById("password").value;

  // Temporary: simple validation
  if (email === "patient@example.com" && password === "password123") {
    // Save patient info to localStorage
    localStorage.setItem("patientData", JSON.stringify({
      name: "Jane Doe",
      email: email,
      phone: "+972 52 123 4567"
    }));
    window.location.href = "index.html"; // Redirect to dashboard/home
  } else {
    errorMsg.textContent = "Invalid email or password!";
  }
});
