<?php include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/color.css">
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/color.js"></script>
<head><meta charset="UTF-8"><title>Sửa Màu</title></head>
<main class="noi-dung-chinh">
	<div class="noi-dung-dashboard">
		<h3>Sửa Màu</h3>
		<form method="POST" action="index.php?c=color&a=sua&id=<?= (int)($current['ma_mau'] ?? 0) ?>" class="form-them">
			<div class="form-row"><label>Tên màu</label><input type="text" name="ten_mau" value="<?= htmlspecialchars($current['ten_mau'] ?? '') ?>" required></div>
			<div class="form-row"><label>Mã HEX</label><input type="text" name="ma_mau_hex" value="<?= htmlspecialchars($current['ma_mau_hex'] ?? '') ?>" required></div>
			<div class="form-actions"><button type="submit" class="them-moi-btn"><i class="fas fa-save"></i> Lưu</button><a href="index.php?c=color&a=index" class="all-btn">Hủy</a></div>
		</form>
	</div>
</main>


