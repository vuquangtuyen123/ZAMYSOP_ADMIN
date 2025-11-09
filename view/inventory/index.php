<?php include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="assets/css/inventory.css">
<link rel="stylesheet" href="assets/css/pagination.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/dashboard.js"></script>

<main class="noi-dung-chinh">
	<div class="noi-dung-dashboard">
		<h3><i class="fas fa-boxes"></i> Quản lý tồn kho</h3>

		<!-- Section: Tìm kiếm -->
		<div class="search-section">
			<h4><i class="fas fa-search"></i> Tìm kiếm</h4>
			<div class="hop-tim-kiem">
				<form method="GET" action="index.php">
					<input type="hidden" name="c" value="inventory">
					<input type="hidden" name="a" value="index">
					<input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Tìm theo tên sản phẩm, size, màu sắc, mã biến thể..." id="searchInput">
					<button type="submit"><i class="fas fa-search"></i> Tìm kiếm</button>
				</form>
				<a href="index.php?c=inventory&a=index" class="nut-tai-lai"><i class="fas fa-redo"></i> Tải lại</a>
			</div>
		</div>

		<!-- Section: Danh sách tồn kho -->
		<div class="inventory-list-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; gap: 8px;">
				<h4><i class="fas fa-list"></i> Danh sách tồn kho</h4>
                <a href="index.php?c=inventory&a=exportCsv" class="export-btn"><i class="fas fa-download"></i> Xuất CSV</a>
			</div>
			
			<?php if (!empty($inventory_list['error'])): ?>
				<div class="result-error">
					<i class="fas fa-exclamation-triangle"></i> Không thể tải danh sách tồn kho.
				</div>
			<?php elseif (empty($inventory_list['data'])): ?>
				<div class="empty-state">
					<i class="fas fa-inbox"></i>
					<p>Không có dữ liệu tồn kho</p>
				</div>
			<?php else: ?>
				<table class="inventory-table">
					<thead>
						<tr>
							<th>Mã biến thể</th>
							<th>Tên sản phẩm</th>
							<th>Size</th>
							<th>Màu sắc</th>
							<th>Tồn kho</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($inventory_list['data'] as $item): ?>
							<tr>
								<td><strong><?= htmlspecialchars($item['ma_bien_the']) ?></strong></td>
								<td><?= htmlspecialchars($item['products']['ten_san_pham'] ?? 'N/A') ?></td>
								<td><?= htmlspecialchars($item['sizes']['ten_size'] ?? 'N/A') ?></td>
								<td><?= htmlspecialchars($item['colors']['ten_mau'] ?? 'N/A') ?></td>
								<td>
									<span class="<?php 
										$stock = (int)($item['ton_kho'] ?? 0);
										if ($stock == 0) echo 'stock-low';
										elseif ($stock < 10) echo 'stock-medium';
										else echo 'stock-high';
									?>">
										<?= $stock ?>
									</span>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				
				<?php if (isset($totalPages) && $totalPages > 1): ?>
				<div class="pagination" style="margin-top:60px; margin-bottom:40px; text-align:center;">
					<?php 
						$cur = isset($page) ? $page : 1;
						$prev = max(1, $cur - 1);
						$next = min($totalPages, $cur + 1);
						$searchParam = !empty($search) ? '&search=' . urlencode($search) : '';
					?>
					<a href="index.php?c=inventory&a=index&page=<?= $prev ?><?= $searchParam ?>" class="<?= $cur <= 1 ? 'disabled' : '' ?>">&lt;</a>
					<?php for ($i = 1; $i <= $totalPages; $i++): ?>
						<a href="index.php?c=inventory&a=index&page=<?= $i ?><?= $searchParam ?>" class="<?= $i == $cur ? 'active' : '' ?>"><?= $i ?></a>
					<?php endfor; ?>
					<a href="index.php?c=inventory&a=index&page=<?= $next ?><?= $searchParam ?>" class="<?= $cur >= $totalPages ? 'disabled' : '' ?>">&gt;</a>
				</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<!-- Section: Upload file -->
		<div class="upload-section">
			<h4><i class="fas fa-upload"></i> Cập nhật tồn kho bằng file</h4>
            <p style="margin-bottom: 15px; color: #6c757d;">
                Hỗ trợ định dạng: <strong>.csv</strong>, <strong>.xlsx</strong> hoặc <strong>.xls</strong>.<br>
                Yêu cầu cột: <strong>Mã biến thể</strong> (cột A), <strong>Số lượng</strong> (cột B). Số lượng sẽ được cộng vào tồn kho hiện tại.<br>
                <strong>Ví dụ:</strong> A2=1, B2=3 (cộng 3 vào tồn kho của biến thể 1).
            </p>
            <div style="margin-bottom: 15px; padding: 12px; background: #f8f9fa; border-radius: 6px; border: 1px solid #dee2e6;">
                <strong style="display: block; margin-bottom: 8px;">Tải file mẫu:</strong>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="index.php?c=inventory&a=downloadSampleExcel" class="btn-sample" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: #28a745; color: white; text-decoration: none; border-radius: 4px; font-weight: 500; transition: all 0.3s;">
                        <i class="fas fa-file-excel"></i> Tải file Excel mẫu (.xlsx)
                    </a>
                    <a href="index.php?c=inventory&a=downloadSampleCsv" class="btn-sample" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: #17a2b8; color: white; text-decoration: none; border-radius: 4px; font-weight: 500; transition: all 0.3s;">
                        <i class="fas fa-file-csv"></i> Tải file CSV mẫu (.csv)
                    </a>
                </div>
            </div>
			<form method="POST" action="index.php?c=inventory&a=upload" enctype="multipart/form-data" class="form-them">
                <div class="form-row">
                    <label><i class="fas fa-file"></i> Chọn file</label>
                    <input type="file" name="file" accept=".csv,.xlsx,.xls" required>
                </div>
				<div class="form-actions">
					<button type="submit" class="them-moi-btn"><i class="fas fa-upload"></i> Tải lên & cập nhật</button>
				</div>
			</form>
		</div>

		<!-- Section: Kết quả upload -->
		<?php if (!empty($results)): ?>
			<div class="upload-section">
				<h4><i class="fas fa-check-circle"></i> Kết quả cập nhật</h4>
				<?php if (!empty($results['error'])): ?>
					<div class="result-error">
						<i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($results['error']) ?>
					</div>
				<?php else: ?>
					<div class="result-summary">
						<strong>Tổng số:</strong> <?= (int)($results['summary']['total'] ?? 0) ?> | 
						<span style="color: #28a745;"><strong>Thành công:</strong> <?= (int)($results['summary']['success'] ?? 0) ?></span> | 
						<span style="color: #dc3545;"><strong>Thất bại:</strong> <?= (int)($results['summary']['failed'] ?? 0) ?></span>
					</div>
					<?php if (!empty($results['details'])): ?>
						<table class="result-table">
							<thead>
								<tr>
									<th>Hàng</th>
									<th>Mã biến thể</th>
									<th>Số lượng</th>
									<th>Kết quả</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($results['details'] as $d): ?>
									<tr>
										<td><?= htmlspecialchars($d['row']) ?></td>
										<td><strong><?= htmlspecialchars($d['ma_bien_the']) ?></strong></td>
										<td><?= htmlspecialchars($d['ton_kho']) ?></td>
										<td>
											<span class="<?= strpos($d['result'], 'OK') !== false ? 'success' : 'error' ?>">
												<?= htmlspecialchars($d['result']) ?>
											</span>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</main>

<style>
.btn-sample:hover {
    opacity: 0.9;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}
</style>