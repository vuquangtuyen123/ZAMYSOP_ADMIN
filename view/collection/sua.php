<?php include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/coupon.css">
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/dashboard.js"></script>

<main class="noi-dung-chinh">
	<div class="noi-dung-dashboard">
		<h3>Sửa bộ sưu tập</h3>
		<form method="POST" action="index.php?c=collection&a=sua&ma_bo_suu_tap=<?= htmlspecialchars($collection['ma_bo_suu_tap'] ?? '') ?>" class="form-them" enctype="multipart/form-data">
			<div class="form-row">
				<label>Tên bộ sưu tập *</label>
				<input type="text" name="ten_bo_suu_tap" value="<?= htmlspecialchars($collection['ten_bo_suu_tap'] ?? '') ?>" required>
			</div>
			<div class="form-row">
				<label>Mô tả</label>
				<textarea name="mo_ta" rows="3"><?= htmlspecialchars($collection['mo_ta'] ?? '') ?></textarea>
			</div>
			
			<!-- Hiển thị ảnh hiện có -->
			<div class="form-row">
				<label>Ảnh hiện có (Click để xóa)</label>
				<div id="current-images" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap:10px; margin-top:10px;">
					<?php 
					$images = $collection['collection_images'] ?? [];
					foreach ($images as $img): 
						$maHinhAnh = $img['ma_hinh_anh'] ?? '';
						$duongDan = $img['duong_dan_anh'] ?? '';
					?>
						<div class="image-item" style="position:relative; display:inline-block;">
							<img src="<?= htmlspecialchars($duongDan) ?>" alt="Collection image" style="width:150px; height:150px; object-fit:cover; border-radius:5px; border:1px solid #ddd;">
							<button type="button" class="delete-image-btn" data-ma-hinh-anh="<?= htmlspecialchars($maHinhAnh) ?>" style="position:absolute; top:5px; right:5px; background:#e74c3c; color:white; border:none; border-radius:50%; width:25px; height:25px; cursor:pointer; font-size:14px;" title="Xóa ảnh">×</button>
						</div>
					<?php endforeach; ?>
					<?php if (empty($images)): ?>
						<p style="color:#999; grid-column:1/-1;">Chưa có ảnh nào</p>
					<?php endif; ?>
				</div>
			</div>

			<!-- Upload ảnh mới -->
			<div class="form-row">
				<label>Thêm ảnh mới (Upload)</label>
				<input type="file" name="images[]" multiple accept="image/*" id="image-upload">
				<small style="color:#666; display:block; margin-top:5px;">Có thể chọn nhiều ảnh cùng lúc</small>
				<div id="image-preview" style="margin-top:15px; display:grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap:10px;"></div>
			</div>
			
			<div class="form-row">
				<label>Hoặc nhập URL ảnh mới (mỗi URL một dòng)</label>
				<textarea name="image_urls" rows="4" placeholder="https://example.com/image1.jpg&#10;https://example.com/image2.jpg"></textarea>
			</div>
			
			<div class="form-row">
				<label>
					<input type="checkbox" name="trang_thai" <?= !empty($collection['trang_thai']) ? 'checked' : '' ?>> Kích hoạt
				</label>
			</div>
			<div class="form-row">
				<button type="submit" class="them-moi-btn" style="float:none; margin-right:10px;">Cập nhật</button>
				<a href="index.php?c=collection&a=index" class="all-btn" style="text-decoration:none; display:inline-block;">Hủy</a>
			</div>
		</form>
	</div>
</main>

<script>
// Preview ảnh mới upload
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

// Xóa ảnh
document.querySelectorAll('.delete-image-btn').forEach(btn => {
	btn.addEventListener('click', function() {
		const maHinhAnh = this.dataset.maHinhAnh;
		if (!confirm('Xóa ảnh này?')) return;
		
		fetch('index.php?c=collection&a=xoaAnh', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: 'ma_hinh_anh=' + encodeURIComponent(maHinhAnh)
		})
		.then(response => response.json())
		.then(data => {
			if (data.ok) {
				this.closest('.image-item').remove();
				// Nếu không còn ảnh nào, hiển thị thông báo
				if (document.querySelectorAll('.image-item').length === 0) {
					document.getElementById('current-images').innerHTML = '<p style="color:#999; grid-column:1/-1;">Chưa có ảnh nào</p>';
				}
			} else {
				alert('Lỗi khi xóa ảnh');
			}
		})
		.catch(error => {
			console.error('Error:', error);
			alert('Lỗi kết nối server');
		});
	});
});
</script>
