document.addEventListener('DOMContentLoaded', function () {
  const typeSelect = document.getElementById('filter-type');
  const inputDate = document.getElementById('filter-date');
  const inputMonth = document.getElementById('filter-month');
  const inputYear = document.getElementById('filter-year');
  const applyBtn = document.getElementById('apply-filter');
  const resetBtn = document.getElementById('reset-filter');
  const emptyDiv = document.getElementById('chartEmpty');
  const canvas = document.getElementById('revenueChart');
  const ctx = canvas.getContext('2d');
  let chart = null;

  // Ẩn/hiện input lọc
  function showInput() {
    inputDate.style.display = 'none';
    inputMonth.style.display = 'none';
    inputYear.style.display = 'none';
    if (typeSelect.value === 'day') inputDate.style.display = 'inline-block';
    if (typeSelect.value === 'month') inputMonth.style.display = 'inline-block';
    if (typeSelect.value === 'year') inputYear.style.display = 'inline-block';
  }

  // Lấy giá trị lọc
  function getFilterValue() {
    if (typeSelect.value === 'day') return inputDate.value;
    if (typeSelect.value === 'month') return inputMonth.value;
    if (typeSelect.value === 'year') return inputYear.value;
    return '';
  }

  // === Load dữ liệu và vẽ biểu đồ doanh thu ===
  async function loadChart() {
    const type = typeSelect.value;
    const value = getFilterValue();

    emptyDiv.style.display = 'none';
    canvas.style.display = 'block';

    const params = new URLSearchParams();
    params.append('c', 'dashboard');
    params.append('a', 'apiRevenue');
    params.append('type', type);
    if (value) params.append('value', value);

    const url = `index.php?${params.toString()}`;
    console.log('[DEBUG] Gọi API:', url);

    try {
      const res = await fetch(url);
      const data = await res.json();

      if (!Array.isArray(data) || data.length === 0) return showEmpty();

      const labels = data.map(d => d.thoigian);
      const values = data.map(d => d.doanh_thu || 0);
      if (values.every(v => v === 0)) return showEmpty();

      if (chart) chart.destroy();
      chart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels,
          datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: values,
            backgroundColor: 'rgba(0,184,148,0.7)',
            borderColor: '#00b894',
            borderWidth: 1,
            borderRadius: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: ctx => `${ctx.parsed.y.toLocaleString('vi-VN')}₫`
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: { callback: v => v.toLocaleString('vi-VN') + '₫' }
            }
          }
        }
      });
    } catch (err) {
      console.error('Lỗi load biểu đồ:', err);
      showEmpty();
    }
  }

  function showEmpty() {
    if (chart) { chart.destroy(); chart = null; }
    canvas.style.display = 'none';
    emptyDiv.style.display = 'block';
  }

  // === Sự kiện lọc toàn bộ dashboard ===
  applyBtn.addEventListener('click', e => {
    e.preventDefault();
    const type = typeSelect.value;
    const value = getFilterValue();

    // ⚡ Giữ nguyên cách load biểu đồ của bạn
    // nhưng thêm đoạn reload trang để toàn dashboard đổi dữ liệu
    const url = `index.php?c=dashboard&a=index&type=${type}&value=${encodeURIComponent(value)}`;
    window.location.href = url; // ✅ CHỈ THÊM DÒNG NÀY
  });

  // Reset bộ lọc
  resetBtn.addEventListener('click', e => {
    e.preventDefault();
    window.location.href = 'index.php?c=dashboard&a=index';
  });

  // Khi đổi loại lọc thì chỉ hiển thị input phù hợp
  typeSelect.addEventListener('change', showInput);

  showInput();
  loadChart();

  // === Biểu đồ tròn danh mục ===
  const catCanvas = document.getElementById('categoryChart');
  const raw = catCanvas.dataset.categories || '[]';
  try {
    const cats = JSON.parse(raw);
    if (Array.isArray(cats) && cats.length) {
      new Chart(catCanvas.getContext('2d'), {
        type: 'pie',
        data: {
          labels: cats.map(c => c.ten_danh_muc || 'Khác'),
          datasets: [{
            data: cats.map(c => Number(c.doanh_thu) || 0),
            backgroundColor: ['#00b894', '#0984e3', '#6c5ce7', '#fdcb6e', '#e17055', '#e84393']
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom' },
            tooltip: {
              callbacks: {
                label: ctx => `${ctx.label}: ${ctx.parsed.toLocaleString('vi-VN')}₫`
              }
            }
          }
        }
      });
    }
  } catch (err) {
    console.error('Lỗi biểu đồ danh mục:', err);
  }
});
