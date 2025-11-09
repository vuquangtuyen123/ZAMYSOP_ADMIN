<?php include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/size.css">
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/size.js"></script>
<head><meta charset="UTF-8"><title>Sửa Size</title></head>
<main class="noi-dung-chinh">
	<div class="noi-dung-dashboard">
		<h3>Sửa Size</h3>
		<form method="POST" action="index.php?c=size&a=sua&id=<?= (int)($current['ma_size'] ?? 0) ?>" class="form-them">
			<div class="form-row"><label>Tên size</label><input type="text" name="ten_size" value="<?= htmlspecialchars($current['ten_size'] ?? '') ?>" required></div>
			<div class="form-actions"><button type="submit" class="them-moi-btn"><i class="fas fa-save"></i> Lưu</button><a href="index.php?c=size&a=index" class="all-btn">Hủy</a></div>
		</form>
	</div>
</main>


