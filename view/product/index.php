<?php include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/product.css">
<link rel="stylesheet" href="assets/css/coupon.css">
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="assets/css/pagination.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/product.js"></script>

<head>
	<meta charset="UTF-8">
	<title>Quản lý Sản phẩm - ZamyShop</title>
	<style>
	/* ==============================
	   TOAST NOTIFICATION (GÓC TRÊN PHẢI)
	   ============================== */
	.toast {
	    visibility: hidden;
	    min-width: 250px;
	    max-width: 320px;
	    background-color: #333;
	    color: #fff;
	    text-align: center;
	    border-radius: 8px;
	    padding: 14px 20px;
	    position: fixed;
	    z-index: 9999;
	    top: 20px;
	    right: 20px;
	    font-size: 15px;
	    font-weight: 500;
	    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
	    transition: all 0.5s ease;
	}

	.toast.show {
	    visibility: visible;
	    opacity: 1;
	    animation: fadeInRight 0.5s, fadeOut 0.5s 2.5s;
	}

	@keyframes fadeInRight {
	    from { opacity: 0; transform: translateX(20px); }
	    to { opacity: 1; transform: translateX(0); }
	}

	@keyframes fadeOut {
	    from { opacity: 1; }
	    to { opacity: 0; }
	}
	</style>
</head>

<main class="noi-dung-chinh">
	<header class="thanh-tieu-de">
		<div class="hop-tim-kiem">
			<form method="GET" action="index.php">
				<input type="hidden" name="c" value="product">
				<input type="hidden" name="a" value="index">
				<input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Tìm sản phẩm (tên)" aria-label="Tìm kiếm">
				<button type="submit"><i class="fas fa-search"></i></button>
			</form>
			<a href="index.php?c=product&a=index&reset=1" class="all-btn">Tải lại</a>
		</div>
		<div class="thong-tin-nguoi-dung">
			<span>Xin chào: <?= htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['user_email']) ?></span>
		</div>
	</header>

	<div class="noi-dung-dashboard">
		<h3>Quản lý Sản phẩm</h3>
		<a href="index.php?c=product&a=them" class="them-moi-btn"><i class="fas fa-plus"></i> Thêm mới</a>
		<table class="news-list">
			<thead>
				<tr>
					<th>Mã</th>
					<th>Tên sản phẩm</th>
					<th>Giá</th> 
					<th>Danh mục</th>
					<th>Trạng thái</th>
					<th>Hành động</th>
				</tr>
			</thead>
			<tbody>
			<?php if (!empty($products)): ?>
				<?php foreach ($products as $p): ?>
					<tr>
						<td><?= htmlspecialchars($p['ma_san_pham']) ?></td>
						<td><?= htmlspecialchars($p['ten_san_pham']) ?></td>
					    <td><?= number_format((float)($p['muc_gia_goc'] ?? 0), 0, ',', '.') ?> đ</td>
						<td><?= htmlspecialchars($categoryMap[$p['ma_danh_muc']] ?? '—') ?></td>
						<td>
							<label class="switch">
								<input type="checkbox" class="toggle-featured" 
									   data-id="<?= (int)$p['ma_san_pham'] ?>"
									   <?= !empty($p['trang_thai_hien_thi']) ? 'checked' : '' ?>>
								<span class="slider"></span>
							</label>
						</td>
						<td>
							<a href="index.php?c=product&a=sua&ma_san_pham=<?= $p['ma_san_pham'] ?>" class="action-link edit-link"><i class="fas fa-edit"></i> Sửa</a>
							<a href="index.php?c=product&a=xoa&ma_san_pham=<?= $p['ma_san_pham'] ?>" class="action-link delete-link" onclick="return confirm('Bạn có chắc muốn xóa?')"><i class="fas fa-trash"></i> Xóa</a>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else: ?>
				<tr><td colspan="6" style="text-align:center;">Không có sản phẩm nào</td></tr>
			<?php endif; ?>
			</tbody>
		</table>

		<div class="pagination" style="margin-top:40px; margin-bottom:40px; text-align:center;">
			<?php if ($totalPages > 1): ?>
				<?php $prevPage = $page > 1 ? $page - 1 : 1; $nextPage = $page < $totalPages ? $page + 1 : $totalPages; ?>
				<a href="index.php?c=product&a=index&page=<?= $prevPage ?><?= $search ? '&search=' . urlencode($search) : '' ?>">&lt;</a>
				<?php for ($i = 1; $i <= $totalPages; $i++): ?>
					<a href="index.php?c=product&a=index&page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
				<?php endfor; ?>
				<a href="index.php?c=product&a=index&page=<?= $nextPage ?><?= $search ? '&search=' . urlencode($search) : '' ?>">&gt;</a>
			<?php endif; ?>
		</div>
	</div>

	<!-- Toast container -->
	<div id="toast" class="toast"></div>
</main>

<script>
// =========================
// TOAST NOTIFICATION
// =========================
function showToast(message, type = 'success') {
    let toast = document.getElementById('toast');
    toast.textContent = message;
    toast.style.backgroundColor = type === 'error' ? '#e53e3e' : '#38a169';
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}

// Hiển thị thông báo từ PHP
<?php if (!empty($message)): ?>
<?php 
$type = ($action ?? '') === 'xoa' ? 'error' : 'success'; 
?>
showToast("<?= addslashes($message) ?>", "<?= $type ?>");
<?php endif; ?>

// Toggle sản phẩm nổi bật
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toggle-featured').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const productId = this.dataset.id;
            const isFeatured = this.checked;

            fetch('index.php?c=product&a=toggleFeatured', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `ma_san_pham=${encodeURIComponent(productId)}&is_featured=${isFeatured ? 'true' : 'false'}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(isFeatured ? 'Đã đặt làm sản phẩm nổi bật' : 'Đã bỏ đánh dấu nổi bật');
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể cập nhật'));
                    this.checked = !isFeatured;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Lỗi kết nối server.');
                this.checked = !isFeatured;
            });
        });
    });
});
</script>
