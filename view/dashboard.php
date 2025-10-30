<?php
$summary         = $summary         ?? ['tong_don_hang' => 0, 'da_thanh_toan' => 0, 'chua_thanh_toan' => 0, 'don_huy' => 0, 'don_hoan' => 0, 'tong_doanh_thu' => 0];
$categoryRevenue = $categoryRevenue ?? [];
$topProducts     = $topProducts     ?? [];
$cancelStats     = $cancelStats     ?? [];
$returnStats     = $returnStats     ?? [];

// ✅ Giữ giá trị lọc hiện tại
$type  = $_GET['type']  ?? 'month';
$value = $_GET['value'] ?? '';

include __DIR__ . '../menu.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Zamy Shop - Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script> <!-- ✅ Dùng chụp PDF -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script> <!-- ✅ Dùng tạo PDF -->
  <link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
  <link rel="stylesheet" href="assets/css/baocao.css">
  <style>
    #export-report, #export-pdf {
      border: none;
      padding: 6px 12px;
      border-radius: 4px;
      cursor: pointer;
      color: #fff;
      font-weight: 500;
      margin-left: 5px;
    }
    #export-report { background: #28a745; }
    #export-pdf { background: #d63031; }
  </style>
</head>

<body>
<div class="container" id="report-container">
  <h2>Bảng điều khiển doanh thu & thống kê</h2>

  <!-- Tổng quan -->
  <div class="stats-grid">
    <div class="stat-card"><h3>Tổng đơn hàng</h3><p><?= $summary['tong_don_hang'] ?></p></div>
    <div class="stat-card completed"><h3>Đã thanh toán</h3><p><?= $summary['da_thanh_toan'] ?></p></div>
    <div class="stat-card pending"><h3>Chưa thanh toán</h3><p><?= $summary['chua_thanh_toan'] ?></p></div>
    <div class="stat-card canceled"><h3>Đơn hủy</h3><p><?= $summary['don_huy'] ?></p></div>
    <div class="stat-card returned"><h3>Đơn hoàn</h3><p><?= $summary['don_hoan'] ?></p></div>
    <div class="stat-card revenue"><h3>Tổng doanh thu</h3><p><?= number_format($summary['tong_doanh_thu'], 0, ',', '.') ?>₫</p></div>
  </div>

  <!-- Biểu đồ doanh thu -->
  <div class="chart-section">
    <h3>Biểu đồ doanh thu</h3>
    <div class="filter-bar">
      <label>Lọc theo:</label>
      <select id="filter-type">
        <option value="day"   <?= $type === 'day' ? 'selected' : '' ?>>Ngày</option>
        <option value="month" <?= $type === 'month' ? 'selected' : '' ?>>Tháng</option>
        <option value="year"  <?= $type === 'year' ? 'selected' : '' ?>>Năm</option>
      </select>

      <input type="date" id="filter-date" value="<?= $type === 'day' ? htmlspecialchars($value) : '' ?>" style="<?= $type === 'day' ? '' : 'display:none;' ?>">
      <input type="month" id="filter-month" value="<?= $type === 'month' ? htmlspecialchars($value) : '' ?>" style="<?= $type === 'month' ? '' : 'display:none;' ?>">
      <input type="number" id="filter-year" min="2000" max="2100" placeholder="Năm" value="<?= $type === 'year' ? htmlspecialchars($value) : '' ?>" style="<?= $type === 'year' ? '' : 'display:none;' ?>">

      <button id="apply-filter">Áp dụng</button>
      <button id="reset-filter">Reset</button>

      <!-- ✅ Các nút xuất -->
      <button id="export-report"><i class="fa fa-file-excel"></i> Xuất Excel</button>
      <button id="export-pdf"><i class="fa fa-file-pdf"></i> Xuất PDF</button>
    </div>

    <div id="chartEmpty" class="chart-empty">Chưa có dữ liệu.</div>
    <canvas id="revenueChart" style="width:100%;max-height:360px;"></canvas>
  </div>

  <!-- 2 cột song song -->
  <div class="double-chart">
    <div class="chart-section">
      <h3>Doanh thu theo danh mục</h3>
      <canvas id="categoryChart"
        data-categories='<?= htmlspecialchars(json_encode($categoryRevenue), ENT_QUOTES, "UTF-8") ?>'
        style="width:100%;max-height:360px;"></canvas>
    </div>

    <div class="table-section">
      <h3>Top 5 sản phẩm bán chạy</h3>
      <?php if (empty($topProducts)): ?>
        <p class="no-data">Chưa có dữ liệu.</p>
      <?php else: ?>
      <table id="table-top">
        <thead><tr><th>Sản phẩm</th><th>Màu</th><th>Size</th><th>Số lượng</th></tr></thead>
        <tbody>
        <?php foreach ($topProducts as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['ten_san_pham']) ?></td>
            <td><?= htmlspecialchars($p['ten_mau']) ?></td>
            <td><?= htmlspecialchars($p['ten_size']) ?></td>
            <td><strong><?= $p['tong_so_luong'] ?? 0 ?></strong></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- Tỷ lệ hủy -->
  <div class="table-section">
    <h3>Top 5 sản phẩm có tỷ lệ hủy cao</h3>
    <?php if (empty($cancelStats)): ?>
      <p class="no-data">Chưa có dữ liệu.</p>
    <?php else: ?>
    <table id="table-cancel">
      <thead><tr><th>Sản phẩm</th><th>Tỷ lệ hủy (%)</th></tr></thead>
      <tbody>
        <?php foreach ($cancelStats as $s): ?>
        <tr><td><?= htmlspecialchars($s['ten_san_pham']) ?></td><td><?= $s['ty_le'] ?>%</td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <!-- Tỷ lệ hoàn -->
  <div class="table-section">
    <h3>Top 5 sản phẩm có tỷ lệ hoàn cao</h3>
    <?php if (empty($returnStats)): ?>
      <p class="no-data">Chưa có dữ liệu.</p>
    <?php else: ?>
    <table id="table-return">
      <thead><tr><th>Sản phẩm</th><th>Tỷ lệ hoàn (%)</th></tr></thead>
      <tbody>
        <?php foreach ($returnStats as $s): ?>
        <tr><td><?= htmlspecialchars($s['ten_san_pham']) ?></td><td><?= $s['ty_le'] ?>%</td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<script src="assets/js/baocao.js"></script>

<!-- === XUẤT EXCEL + PDF === -->
<script>
/* ====== XUẤT EXCEL ====== */
document.getElementById('export-report').addEventListener('click', function () {
  const wb = XLSX.utils.book_new();

  const today = new Date();
  const reportTime = today.toLocaleDateString('vi-VN');
  const title = [
    ["BÁO CÁO DOANH THU ZAMY SHOP"],
    ["Thời gian báo cáo:", "<?= ucfirst($type) ?> - <?= htmlspecialchars($value ?: 'Toàn bộ') ?>"],
    ["Ngày xuất:", reportTime],
    [],
    ["Chỉ tiêu", "Giá trị"]
  ];

  const summary = [
    ["Tổng đơn hàng", "<?= $summary['tong_don_hang'] ?>"],
    ["Đã thanh toán", "<?= $summary['da_thanh_toan'] ?>"],
    ["Chưa thanh toán", "<?= $summary['chua_thanh_toan'] ?>"],
    ["Đơn hủy", "<?= $summary['don_huy'] ?>"],
    ["Đơn hoàn", "<?= $summary['don_hoan'] ?>"],
    ["Tổng doanh thu", "<?= number_format($summary['tong_doanh_thu'], 0, ',', '.') ?>₫"]
  ];

  const sheet = XLSX.utils.aoa_to_sheet([...title, ...summary]);
  XLSX.utils.book_append_sheet(wb, sheet, "Tổng quan");

  const top = XLSX.utils.table_to_sheet(document.getElementById('table-top'));
  XLSX.utils.book_append_sheet(wb, top, "Top sản phẩm");

  const cancel = XLSX.utils.table_to_sheet(document.getElementById('table-cancel'));
  XLSX.utils.book_append_sheet(wb, cancel, "Tỷ lệ hủy");

  const ret = XLSX.utils.table_to_sheet(document.getElementById('table-return'));
  XLSX.utils.book_append_sheet(wb, ret, "Tỷ lệ hoàn");

  const fileName = `BaoCao_ZamyShop_${today.getFullYear()}-${today.getMonth()+1}-${today.getDate()}.xlsx`;
  XLSX.writeFile(wb, fileName);
});

/* ====== XUẤT PDF ====== */
document.getElementById('export-pdf').addEventListener('click', async function () {
  const { jsPDF } = window.jspdf;
  const pdf = new jsPDF('p', 'mm', 'a4');

  const container = document.getElementById('report-container');
  const canvas = await html2canvas(container, { scale: 2 });
  const imgData = canvas.toDataURL('image/png');

  const pageWidth = pdf.internal.pageSize.getWidth();
  const pageHeight = pdf.internal.pageSize.getHeight();
  const imgHeight = canvas.height * pageWidth / canvas.width;

  pdf.addImage(imgData, 'PNG', 0, 0, pageWidth, imgHeight);
  pdf.save(`BaoCao_ZamyShop_${new Date().toLocaleDateString('vi-VN')}.pdf`);
});
</script>
</body>
</html>
