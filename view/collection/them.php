<?php include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/coupon.css">
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/dashboard.js"></script>

<main class="noi-dung-chinh">
	<div class="noi-dung-dashboard">
		<h3>Thêm bộ sưu tập</h3>
		<form method="POST" action="index.php?c=collection&a=them" class="form-them" enctype="multipart/form-data">
			<div class="form-row">
				<label>Tên bộ sưu tập *</label>
				<input type="text" name="ten_bo_suu_tap" required>
			</div>
			<div class="form-row">
				<label>Mô tả</label>
				<textarea name="mo_ta" rows="3"></textarea>
			</div>
			<div class="form-row">
				<label>Hình ảnh (Upload nhiều ảnh)</label>
				<input type="file" name="images[]" multiple accept="image/*" id="image-upload">
				<small style="color:#666; display:block; margin-top:5px;">Có thể chọn nhiều ảnh cùng lúc</small>
				<div id="image-preview" style="margin-top:15px; display:grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap:10px;"></div>
			</div>
			<div class="form-row">
				<label>Hoặc nhập URL ảnh (mỗi URL một dòng)</label>
				<textarea name="image_urls" rows="4" placeholder="https://example.com/image1.jpg&#10;https://example.com/image2.jpg"></textarea>
			</div>
			<div class="form-row">
				<label>
					<input type="checkbox" name="trang_thai" checked> Kích hoạt
				</label>
			</div>
			<div class="form-row">
				<button type="submit" class="them-moi-btn" style="float:none; margin-right:10px;">Thêm</button>
				<a href="index.php?c=collection&a=index" class="all-btn" style="text-decoration:none; display:inline-block;">Hủy</a>
			</div>
		</form>
	</div>
</main>

<script>
document.getElementById('image-upload').addEventListener('change', function(e) {
	const preview = document.getElementById('image-preview');
	preview.innerHTML = '';
	
	if (e.target.files) {
		Array.from(e.target.files).forEach(file => {
			if (file.type.startsWith('image/')) {
				const reader = new FileReader();
				reader.onload = function(e) {
					const img = document.createElement('img');
					img.src = e.target.result;
					img.style.width = '150px';
					img.style.height = '150px';
					img.style.objectFit = 'cover';
					img.style.borderRadius = '5px';
					img.style.border = '1px solid #ddd';
					preview.appendChild(img);
				};
				reader.readAsDataURL(file);
			}
		});
	}
});
</script>
