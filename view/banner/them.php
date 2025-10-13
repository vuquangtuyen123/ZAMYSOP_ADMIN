
<?php include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="assets/css/banner.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/danhmuc.js"></script>
<main class="noi-dung-chinh">
    <div class="noi-dung-dashboard">
        <h3>Thêm Banner</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="hinh_anh"><i class="fas fa-image"></i> Chọn ảnh banner</label>
                <input type="file" name="hinh_anh" id="hinh_anh" accept="image/*" required>
            </div>
            <div class="form-group">
                <label for="trang_thai">
                    <input type="checkbox" name="trang_thai" id="trang_thai" value="1" checked>
                    Hiển thị banner
                </label>
            </div>
            <button type="submit" class="them-moi-btn"><i class="fas fa-plus"></i> Thêm mới</button>
        </form>
    </div>
</main>