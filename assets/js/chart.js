// Automatically adapt chart colors to theme
const textColor = getComputedStyle(document.body).getPropertyValue('--text-color').trim();
const gridColor = getComputedStyle(document.body).getPropertyValue('--muted-color').trim();

const myChart = new Chart(ctx, {
  type: 'bar',
  data: { /* your chart data */ },
  options: {
    plugins: {
      legend: { labels: { color: textColor } }
    },
    scales: {
      x: { ticks: { color: textColor }, grid: { color: gridColor } },
      y: { ticks: { color: textColor }, grid: { color: gridColor } }
    }
  }
});
