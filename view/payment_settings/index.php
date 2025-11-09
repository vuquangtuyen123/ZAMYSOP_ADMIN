<?php include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/coupon.css">
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="assets/css/pagination.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/dashboard.js"></script>

<head>
	<meta charset="UTF-8">
	<title>Cài đặt phương thức thanh toán</title>
</head>
<main class="noi-dung-chinh">
	<header class="thanh-tieu-de">
		<div class="hop-tim-kiem">
			<div style="display:flex; align-items:center;">
				<h2 style="margin:0; padding-right:20px;">Cài đặt phương thức thanh toán</h2>
			</div>
		</div>
		<div class="thong-tin-nguoi-dung">
			<span><?= htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['user_email'] ?? '') ?></span>
		</div>
	</header>

	<div class="noi-dung-dashboard">
		<p class="text-muted" style="margin-bottom:20px; color:#666;">Bật/tắt các phương thức thanh toán sẽ hiển thị cho khách hàng. Chỉ các phương thức được bật mới xuất hiện trong app.</p>
		
		<?php if (empty($paymentMethods)): ?>
			<div class="alert alert-info" style="padding:12px; margin:10px 0; border-radius:5px; background:#d1ecf1; color:#0c5460;">
				Chưa có phương thức thanh toán nào. Vui lòng thêm phương thức mới trong database.
			</div>
		<?php else: ?>
			<table class="news-list">
				<thead>
					<tr>
						<th>Mã phương thức</th>
						<th>Tên phương thức</th>
						<th>Mô tả</th>
						<th>Icon</th>
						<th>Thứ tự</th>
						<th>Trạng thái</th>
						<th>Hành động</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($paymentMethods as $method): ?>
						<tr>
							<td><?= htmlspecialchars($method['ma_phuong_thuc']) ?></td>
							<td><?= htmlspecialchars($method['ten_phuong_thuc']) ?></td>
							<td><?= htmlspecialchars($method['mo_ta'] ?? '—') ?></td>
							<td><?= htmlspecialchars($method['icon'] ?? '—') ?></td>
							<td><?= htmlspecialchars($method['thu_tu_hien_thi']) ?></td>
							<td>
								<label class="switch">
									<input type="checkbox" class="toggle-payment-status" 
										   data-code="<?= htmlspecialchars($method['ma_phuong_thuc']) ?>"
										   data-name="<?= htmlspecialchars($method['ten_phuong_thuc']) ?>"
										   <?= $method['da_kich_hoat'] ? 'checked' : '' ?>>
									<span class="slider"></span>
								</label>
							</td>
							<td>
								<span style="color:#666; font-size:12px;">Chỉ có thể bật/tắt</span>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toggle-payment-status').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const code = this.dataset.code;
            const name = this.dataset.name || code;
            const isActive = this.checked;
            const originalState = !isActive; // Trạng thái ban đầu (trước khi toggle)

            // Nếu đang tắt (từ bật -> tắt), hiển thị xác nhận
            if (!isActive) {
                if (!confirm(`Bạn có chắc muốn tắt phương thức thanh toán "${name}"?\n\nPhương thức này sẽ không hiển thị cho khách hàng nữa.`)) {
                    // Người dùng hủy, revert toggle
                    this.checked = originalState;
                    return;
                }
            }

            fetch('index.php?c=payment_settings&a=updateStatus', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `code=${encodeURIComponent(code)}&is_active=${isActive ? 'true' : 'false'}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Không cần cập nhật text nữa
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể cập nhật'));
                    // Revert the toggle if update failed
                    this.checked = originalState;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Lỗi kết nối server.');
                // Revert the toggle on network error
                this.checked = originalState;
            });
        });
    });
});
</script>
