<?php include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/product.css">
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/product.js"></script>

<main class="noi-dung-chinh">
	<div class="noi-dung-dashboard">
		<h3>Sửa sản phẩm</h3>
        <form method="POST" action="index.php?c=product&a=sua&ma_san_pham=<?= htmlspecialchars($product['ma_san_pham']) ?>" class="form-them" enctype="multipart/form-data">
			<div class="form-row">
				<label>Tên sản phẩm</label>
				<input type="text" name="ten_san_pham" value="<?= htmlspecialchars($product['ten_san_pham'] ?? '') ?>" required>
			</div>
			<div class="form-row">
				<label>Mô tả</label>
				<textarea name="mo_ta_san_pham" rows="4"><?= htmlspecialchars($product['mo_ta_san_pham'] ?? '') ?></textarea>
			</div>
			<div class="form-row">
				<label>Giá gốc (VND)</label>
				<input type="number" name="muc_gia_goc" min="0" step="1000" value="<?= htmlspecialchars($product['muc_gia_goc'] ?? 0) ?>">
			</div>
			<div class="form-row">
				<label>Giá bán (VND)</label>
				<input type="number" name="gia_ban" min="0" step="1000" value="<?= htmlspecialchars($product['gia_ban'] ?? 0) ?>" required>
			</div>
			<div class="form-row">
				<label>Số lượng đặt tối thiểu</label>
				<input type="number" name="so_luong_dat_toi_thieu" min="1" value="<?= htmlspecialchars($product['so_luong_dat_toi_thieu'] ?? 1) ?>">
			</div>
			<div class="form-row">
				<label>Danh mục</label>
				<select name="ma_danh_muc" required>
					<?php foreach ($danh_sach_danh_muc as $cat): ?>
						<option value="<?= $cat['ma_danh_muc'] ?>" <?= ($product['ma_danh_muc'] ?? null) == $cat['ma_danh_muc'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['ten_danh_muc']) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php /* Tạm thời ẩn - chưa có chức năng xử lý
			<div class="form-row">
				<label>Mã bộ sưu tập (tuỳ chọn)</label>
				<input type="text" name="ma_bo_suu_tap" value="<?= htmlspecialchars($product['ma_bo_suu_tap'] ?? '') ?>">
			</div>
			*/ ?>
			<div class="form-row">
				<label>Hiển thị</label>
				<input type="checkbox" name="trang_thai_hien_thi" value="1" <?= !empty($product['trang_thai_hien_thi']) ? 'checked' : '' ?> >
			</div>

        <div class="form-row">
            <label>Hình ảnh sản phẩm hiện tại</label>
            <div id="existing-images" style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:15px;">
                <?php if (!empty($images)): ?>
                    <?php foreach ($images as $img): ?>
                        <div style="position:relative; border:1px solid #ddd; border-radius:6px; padding:8px; background:#f9f9f9;">
                            <img src="<?= htmlspecialchars($img['duong_dan_anh']) ?>" alt="Hình sản phẩm" style="width:120px; height:120px; object-fit:cover; border-radius:4px;">
                            <a href="index.php?c=product&a=xoaImage&ma_hinh_anh=<?= (int)$img['ma_hinh_anh'] ?>&ma_san_pham=<?= (int)$product['ma_san_pham'] ?>" 
                               onclick="return confirm('Xóa hình ảnh này?')" 
                               style="position:absolute; top:5px; right:5px; background:#dc3545; color:#fff; width:24px; height:24px; border-radius:50%; display:flex; align-items:center; justify-content:center; text-decoration:none; font-size:14px;" 
                               title="Xóa">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color:#888;">Chưa có hình ảnh nào</p>
                <?php endif; ?>
            </div>
            <label>Thêm hình ảnh mới (có thể chọn nhiều)</label>
            <input type="file" name="images[]" accept="image/*" multiple id="image-input" onchange="previewNewImages(this)">
            <div id="new-images-preview" style="display:flex; flex-wrap:wrap; gap:10px; margin-top:10px;"></div>
        </div>

			<h4>Biến thể (Màu sắc / Size / Tồn kho)</h4>
			<div id="variant-list">
				<?php if (!empty($variants)): ?>
					<?php foreach ($variants as $v): ?>
					<div class="form-row variant-row">
						<label>Màu</label>
						<select name="variant_color[]">
							<option value="">-- Chọn màu --</option>
							<?php foreach (($colors ?? []) as $cl): ?>
								<option value="<?= (int)$cl['ma_mau'] ?>" <?= (int)$cl['ma_mau'] === (int)$v['ma_mau'] ? 'selected' : '' ?>><?= htmlspecialchars($cl['ten_mau']) ?></option>
							<?php endforeach; ?>
						</select>
						<label>Size</label>
						<select name="variant_size[]">
							<option value="">-- Chọn size --</option>
							<?php foreach (($sizes ?? []) as $sz): ?>
								<option value="<?= (int)$sz['ma_size'] ?>" <?= (int)$sz['ma_size'] === (int)$v['ma_size'] ? 'selected' : '' ?>><?= htmlspecialchars($sz['ten_size']) ?></option>
							<?php endforeach; ?>
						</select>
						<label>Tồn kho</label>
						<input type="number" name="variant_stock[]" min="0" value="<?= (int)($v['ton_kho'] ?? 0) ?>" style="max-width:140px;">
					</div>
					<?php endforeach; ?>
				<?php else: ?>
					<div class="form-row variant-row">
						<label>Màu</label>
						<select name="variant_color[]">
							<option value="">-- Chọn màu --</option>
							<?php foreach (($colors ?? []) as $cl): ?>
								<option value="<?= (int)$cl['ma_mau'] ?>"><?= htmlspecialchars($cl['ten_mau']) ?></option>
							<?php endforeach; ?>
						</select>
						<label>Size</label>
						<select name="variant_size[]">
							<option value="">-- Chọn size --</option>
							<?php foreach (($sizes ?? []) as $sz): ?>
								<option value="<?= (int)$sz['ma_size'] ?>"><?= htmlspecialchars($sz['ten_size']) ?></option>
							<?php endforeach; ?>
						</select>
						<label>Tồn kho</label>
						<input type="number" name="variant_stock[]" min="0" value="0" style="max-width:140px;">
					</div>
				<?php endif; ?>
			</div>
			<div class="form-actions" style="margin-top:8px;">
				<button type="button" class="all-btn" onclick="addVariantRow()"><i class="fas fa-plus"></i> Thêm biến thể</button>
			</div>
			<div class="form-actions">
				<button type="submit" class="them-moi-btn"><i class="fas fa-save"></i> Lưu</button>
				<a href="index.php?c=product&a=index" class="all-btn">Hủy</a>
			</div>
		</form>
	</div>
</main>

<script>
function addVariantRow() {
	const container = document.getElementById('variant-list');
	const first = container.querySelector('.variant-row');
	const row = first.cloneNode(true);
	row.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
	row.querySelector('input[name="variant_stock[]"]').value = 0;
	container.appendChild(row);
}

function previewNewImages(input) {
	const preview = document.getElementById('new-images-preview');
	preview.innerHTML = '';
	if (input.files && input.files.length > 0) {
		Array.from(input.files).forEach((file, index) => {
			if (file.type.startsWith('image/')) {
				const reader = new FileReader();
				reader.onload = function(e) {
					const div = document.createElement('div');
					div.style.position = 'relative';
					div.style.border = '1px solid #ddd';
					div.style.borderRadius = '6px';
					div.style.padding = '8px';
					div.style.background = '#f9f9f9';
					const img = document.createElement('img');
					img.src = e.target.result;
					img.style.width = '120px';
					img.style.height = '120px';
					img.style.objectFit = 'cover';
					img.style.borderRadius = '4px';
					const removeBtn = document.createElement('button');
					removeBtn.type = 'button';
					removeBtn.innerHTML = '<i class="fas fa-times"></i>';
					removeBtn.style.position = 'absolute';
					removeBtn.style.top = '5px';
					removeBtn.style.right = '5px';
					removeBtn.style.background = '#dc3545';
					removeBtn.style.color = '#fff';
					removeBtn.style.width = '24px';
					removeBtn.style.height = '24px';
					removeBtn.style.borderRadius = '50%';
					removeBtn.style.border = 'none';
					removeBtn.style.cursor = 'pointer';
					removeBtn.onclick = function() {
						div.remove();
						const dt = new DataTransfer();
						Array.from(input.files).forEach((f, i) => {
							if (i !== index) dt.items.add(f);
						});
						input.files = dt.files;
					};
					div.appendChild(img);
					div.appendChild(removeBtn);
					preview.appendChild(div);
				};
				reader.readAsDataURL(file);
			}
		});
	}
}
</script>

