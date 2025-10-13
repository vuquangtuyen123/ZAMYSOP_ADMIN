<?php
include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/banner.css">
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/danhmuc.js"></script>
<script src="assets/js/banner.js"></script>
<main class="noi-dung-chinh">
    <div class="noi-dung-dashboard">
        <div class="thanh-tieu-de">
            <h3>Quản lý Banner</h3>
            <a href="index.php?c=banner&a=them" class="them-moi-btn"><i class="fas fa-plus"></i> Thêm mới</a>
        </div>
        <div class="banner-grid">
            <?php if (!empty($banners)): ?>
                <?php foreach ($banners as $item): ?>
                    <div class="banner-item">
                        <img src="<?= htmlspecialchars($item['hinh_anh']) ?>" alt="Banner" class="banner-img">
                        <label class="switch" title="Bật/Tắt hiển thị">
                            <input type="checkbox" class="toggle-banner-status" data-id="<?= $item['ma_banner'] ?>" <?= $item['trang_thai'] ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                        <a href="index.php?c=banner&a=xoa&ma_banner=<?= $item['ma_banner'] ?>" class="delete-banner" onclick="return confirm('Bạn có chắc muốn xóa banner này?')">
                            <i class="fas fa-trash"></i>
                        </a>
                        <div class="banner-date">
                            <i class="fa-regular fa-calendar"></i>
                            <?= isset($item['ngay_tao']) ? date('d/m/Y H:i', strtotime($item['ngay_tao'])) : '' ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center;">Không có banner nào</p>
            <?php endif; ?>
        </div>
    </div>
</main>
<script>
<?php if (!empty($message)): ?>
    window.addEventListener('DOMContentLoaded', function() {
        showToast("<?= addslashes($message) ?>");
    });
<?php endif; ?>
</script>