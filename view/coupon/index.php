<?php include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/coupon.css">
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="assets/css/pagination.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/coupon.js"></script>

<head>
	<meta charset="UTF-8">
	<title>Quản lý Mã giảm giá -ZamyShop</title>
</head>
<main class="noi-dung-chinh">
	<header class="thanh-tieu-de">
		<div class="hop-tim-kiem">
			<form method="GET" action="index.php">
				<input type="hidden" name="c" value="coupon">
				<input type="hidden" name="a" value="index">
				<input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Tìm theo mã giảm giá">
				<button type="submit"><i class="fas fa-search"></i></button>
			</form>
			<a href="index.php?c=coupon&a=index&reset=1" class="all-btn">Tải lại</a>
		</div>
		<div class="thong-tin-nguoi-dung">
			<span><?= htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['user_email'] ?? '') ?></span>
		</div>
	</header>

	<div class="noi-dung-dashboard">
		<h3>Mã giảm giá</h3>
		<a href="index.php?c=coupon&a=them" class="them-moi-btn"><i class="fas fa-plus"></i> Thêm mới</a>
		<table class="news-list">
			<thead>
				<tr>
					<th>Code</th>
					<th>Mô tả</th>
					<th>Loại</th>
					<th>Mức giảm</th>
					<th>Hiệu lực</th>
					<th>SL ban đầu / đã dùng</th>
					<th>Trạng thái</th>
					<th>Hành động</th>
				</tr>
			</thead>
			<tbody>
			<?php if (!empty($coupons)): ?>
				<?php foreach ($coupons as $c): ?>
					<tr>
					<td><?= htmlspecialchars($c['code'] ?? '') ?></td>
					<td><?= htmlspecialchars($c['mo_ta'] ?? '') ?></td>
					<td><?= ($c['loai_giam_gia'] === 'fixed') ? 'Số tiền' : 'Phần trăm' ?></td>
						<td>
						<?php if (($c['loai_giam_gia'] ?? '') === 'fixed'): ?>
							<?= number_format((float)($c['muc_giam_gia'] ?? 0), 0, ',', '.') ?> đ
							<?php else: ?>
							<?= (float)($c['muc_giam_gia'] ?? 0) ?>%
							<?php endif; ?>
						</td>
						<td>
							<?php 
							$startRaw = $c['ngay_bat_dau'] ?? '';
							$endRaw = $c['ngay_ket_thuc'] ?? '';
							// Hiển thị trực tiếp giá trị từ Supabase, không convert timezone
							// Chỉ format lại cho đẹp
							if ($startRaw) {
								// Parse datetime và format lại, không đổi timezone
								try {
									$dt = new DateTime($startRaw);
									$startFmt = $dt->format('d/m/Y H:i');
								} catch (Exception $e) {
									// Fallback: format trực tiếp
									$startFmt = date('d/m/Y H:i', strtotime($startRaw));
								}
							} else {
								$startFmt = '—';
							}
							if ($endRaw) {
								try {
									$dt = new DateTime($endRaw);
									$endFmt = $dt->format('d/m/Y H:i');
								} catch (Exception $e) {
									$endFmt = date('d/m/Y H:i', strtotime($endRaw));
								}
							} else {
								$endFmt = '—';
							}
							?>
							<?= htmlspecialchars($startFmt) ?> → <?= htmlspecialchars($endFmt) ?>
						</td>
					<td><?= htmlspecialchars(($c['so_luong_ban_dau'] ?? '—')) ?> / <?= htmlspecialchars(($c['so_luong_da_dung'] ?? 0)) ?></td>
                        <td>
                            <label class="switch">
                                <input type="checkbox" class="toggle-status" data-id="<?= (int)$c['ma_giam_gia'] ?>" <?= !empty($c['trang_thai_kich_hoat']) ? 'checked' : '' ?>>
                                <span class="slider"></span>
                            </label>
                        </td>
						<td>
						<a href="index.php?c=coupon&a=sua&ma_giam_gia=<?= $c['ma_giam_gia'] ?>" class="action-link edit-link"><i class="fas fa-edit"></i> Sửa</a>
						<a href="index.php?c=coupon&a=xoa&ma_giam_gia=<?= $c['ma_giam_gia'] ?>" class="action-link delete-link" onclick="return confirm('Xóa mã này?')"><i class="fas fa-trash"></i> Xóa</a>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else: ?>
				<tr><td colspan="8" style="text-align:center;">Chưa có mã giảm giá</td></tr>
			<?php endif; ?>
			</tbody>
		</table>

		<?php if ($totalPages > 1): ?>
		<div class="pagination" style="margin-top:40px; margin-bottom:40px; text-align:center;">
			<?php 
			$prev = max(1, ($page ?? 1) - 1);
			$next = min($totalPages, ($page ?? 1) + 1);
			?>
			<a href="index.php?c=coupon&a=index&page=<?= $prev ?><?= $search ? '&search=' . urlencode($search) : '' ?>">&lt;</a>
			<?php for ($i = 1; $i <= $totalPages; $i++): ?>
				<a href="index.php?c=coupon&a=index&page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="<?= $i == ($page ?? 1) ? 'active' : '' ?>">
					<?= $i ?>
				</a>
			<?php endfor; ?>
			<a href="index.php?c=coupon&a=index&page=<?= $next ?><?= $search ? '&search=' . urlencode($search) : '' ?>">&gt;</a>
		</div>
		<?php endif; ?>
	</div>
</main>

