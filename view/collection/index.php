<?php include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/coupon.css">
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="assets/css/pagination.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/dashboard.js"></script>

<head>
	<meta charset="UTF-8">
	<title>Quản lý Bộ sưu tập</title>
</head>
<main class="noi-dung-chinh">
	<header class="thanh-tieu-de">
		<div class="hop-tim-kiem">
			<form method="GET" action="index.php">
				<input type="hidden" name="c" value="collection">
				<input type="hidden" name="a" value="index">
				<input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Tìm theo tên bộ sưu tập">
				<button type="submit"><i class="fas fa-search"></i></button>
			</form>
			<a href="index.php?c=collection&a=index&reset=1" class="all-btn">Tải lại</a>
		</div>
		<div class="thong-tin-nguoi-dung">
			<span><?= htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['user_email'] ?? '') ?></span>
		</div>
	</header>

	<div class="noi-dung-dashboard">
		<h3>Bộ sưu tập</h3>
		<a href="index.php?c=collection&a=them" class="them-moi-btn"><i class="fas fa-plus"></i> Thêm mới</a>
		
		<?php if (!empty($message)): ?>
			<div class="alert alert-<?= strpos($message, 'thành công') !== false ? 'success' : 'danger' ?>" style="padding:12px; margin:10px 0; border-radius:5px;">
				<?= htmlspecialchars($message) ?>
			</div>
		<?php endif; ?>

		<table class="news-list">
			<thead>
				<tr>
					<th>Hình ảnh</th>
					<th>Tên bộ sưu tập</th>
					<th>Mô tả</th>
					<th>Trạng thái</th>
					<th>Hành động</th>
				</tr>
			</thead>
			<tbody>
			<?php if (!empty($collections)): ?>
				<?php foreach ($collections as $c): ?>
					<tr>
						<td>
							<?php 
							$images = $c['collection_images'] ?? [];
							if (!empty($images)): 
								$firstImage = $images[0]['duong_dan_anh'] ?? '';
								$imageCount = count($images);
							?>
								<div style="position:relative;">
									<img src="<?= htmlspecialchars($firstImage) ?>" alt="<?= htmlspecialchars($c['ten_bo_suu_tap']) ?>" style="width:80px; height:80px; object-fit:cover; border-radius:5px;">
									<?php if ($imageCount > 1): ?>
										<span style="position:absolute; bottom:0; right:0; background:rgba(0,0,0,0.7); color:white; padding:2px 6px; border-radius:3px; font-size:11px;">+<?= $imageCount - 1 ?></span>
									<?php endif; ?>
								</div>
							<?php else: ?>
								<span style="color:#999;">Chưa có ảnh</span>
							<?php endif; ?>
						</td>
						<td><?= htmlspecialchars($c['ten_bo_suu_tap'] ?? '') ?></td>
						<td><?= htmlspecialchars($c['mo_ta'] ?? '—') ?></td>
                        <td>
                            <label class="switch">
                                <input type="checkbox" class="toggle-status" data-id="<?= (int)$c['ma_bo_suu_tap'] ?>" <?= !empty($c['trang_thai']) ? 'checked' : '' ?>>
                                <span class="slider"></span>
                            </label>
                        </td>
						<td>
							<a href="index.php?c=collection&a=sua&ma_bo_suu_tap=<?= $c['ma_bo_suu_tap'] ?>" class="action-link edit-link"><i class="fas fa-edit"></i> Sửa</a>
						<?php if (can('product.crud')): ?>
<a href="index.php?c=collection&a=xoa&ma_bo_suu_tap=<?= $c['ma_bo_suu_tap'] ?>" class="action-link delete-link" onclick="return confirm('Xóa bộ sưu tập này?')"><i class="fas fa-trash"></i> Xóa</a>
<?php endif; ?>

					</tr>
				<?php endforeach; ?>
			<?php else: ?>
				<tr><td colspan="5" style="text-align:center;">Chưa có bộ sưu tập</td></tr>
			<?php endif; ?>
			</tbody>
		</table>

		<?php if ($totalPages > 1): ?>
		<div class="pagination" style="margin-top:40px; margin-bottom:40px; text-align:center;">
			<?php 
			$prev = max(1, ($page ?? 1) - 1);
			$next = min($totalPages, ($page ?? 1) + 1);
			?>
			<a href="index.php?c=collection&a=index&page=<?= $prev ?><?= $search ? '&search=' . urlencode($search) : '' ?>">&lt;</a>
			<?php for ($i = 1; $i <= $totalPages; $i++): ?>
				<a href="index.php?c=collection&a=index&page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="<?= $i == ($page ?? 1) ? 'active' : '' ?>">
					<?= $i ?>
				</a>
			<?php endfor; ?>
			<a href="index.php?c=collection&a=index&page=<?= $next ?><?= $search ? '&search=' . urlencode($search) : '' ?>">&gt;</a>
		</div>
		<?php endif; ?>
	</div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toggle-status').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const id = this.dataset.id;
            const checked = this.checked;
            
            fetch('index.php?c=collection&a=toggleStatus', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `ma_bo_suu_tap=${id}&value=${checked ? 1 : 0}`
            })
            .then(response => response.json())
            .then(data => {
                if (!data.ok) {
                    alert('Lỗi khi cập nhật trạng thái');
                    this.checked = !checked;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Lỗi kết nối server');
                this.checked = !checked;
            });
        });
    });
});
</script>

