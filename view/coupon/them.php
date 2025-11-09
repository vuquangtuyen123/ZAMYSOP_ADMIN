<?php include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/coupon.css">
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/coupon.js"></script>

<main class="noi-dung-chinh">
	<div class="noi-dung-dashboard">
		<h3>Thêm mã giảm giá</h3>
		<?php if (!empty($_SESSION['message'])): ?>
			<div class="message <?= strpos($_SESSION['message'], 'thành công') !== false ? 'success' : 'error' ?>" style="margin-bottom: 20px; padding: 10px; border-radius: 4px; background: <?= strpos($_SESSION['message'], 'thành công') !== false ? '#d4edda' : '#f8d7da' ?>; color: <?= strpos($_SESSION['message'], 'thành công') !== false ? '#155724' : '#721c24' ?>;">
				<?= htmlspecialchars($_SESSION['message']) ?>
			</div>
			<?php unset($_SESSION['message']); ?>
		<?php endif; ?>
		<form method="POST" action="index.php?c=coupon&a=them" class="form-them" id="couponForm">
			<div class="form-row">
				<label>Code</label>
				<input type="text" name="code" required>
			</div>
			<div class="form-row">
				<label>Nội dung</label>
				<input type="text" name="noi_dung">
			</div>
			<div class="form-row">
				<label>Mô tả</label>
				<textarea name="mo_ta" rows="3"></textarea>
			</div>
			<div class="form-row">
				<label>Loại giảm</label>
				<select name="loai_giam_gia">
					<option value="percentage">Phần trăm (%)</option>
					<option value="fixed">Số tiền (VND)</option>
				</select>
			</div>
			<div class="form-row">
				<label>Mức giảm</label>
				<input type="number" name="muc_giam_gia" step="0.01" min="0" required>
			</div>
			<div class="form-row">
				<label>Ngày bắt đầu</label>
				<input type="datetime-local" name="ngay_bat_dau" id="ngay_bat_dau">
				<small id="ngay_bat_dau_error" style="color: #dc3545; display: none;"></small>
			</div>
			<div class="form-row">
				<label>Ngày kết thúc</label>
				<input type="datetime-local" name="ngay_ket_thuc" id="ngay_ket_thuc">
				<small id="ngay_ket_thuc_error" style="color: #dc3545; display: none;"></small>
			</div>
			<div class="form-row">
				<label>SL ban đầu (giới hạn dùng)</label>
				<input type="number" name="so_luong_ban_dau" min="0">
			</div>
			<div class="form-row">
				<label>Đơn giá tối thiểu áp dụng</label>
				<input type="number" name="don_gia_toi_thieu" min="0" step="1000">
			</div>
			<div class="form-row">
				<label>Kích hoạt</label>
				<input type="checkbox" name="trang_thai_kich_hoat" value="1" checked>
			</div>
			<div class="form-actions">
				<button type="submit" class="them-moi-btn"><i class="fas fa-save"></i> Lưu</button>
				<a href="index.php?c=coupon&a=index" class="all-btn">Hủy</a>
			</div>
		</form>
	</div>
</main>

