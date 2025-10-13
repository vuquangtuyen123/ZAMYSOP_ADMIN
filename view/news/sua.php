 <!-- Include thanh menu có thể tái sử dụng -->
      <?php include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/news.css">
<!-- CSS cho giao diện dashboard -->
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<!-- Font Awesome cho icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Dashboard JavaScript - Logic xử lý tương tác -->
<script src="assets/js/dashboard.js"></script>
<!-- JavaScript cho tin tức -->
 <script src="assets/js/news.js"></script>
<script src="assets/js/danhmuc.js"></script>

<div class="noi-dung-chinh">
    <header class="thanh-tieu-de">
        <div class="hop-tim-kiem">
            <input type="text" placeholder="Tìm kiếm tin tức" aria-label="Tìm kiếm tin tức">
            <i class="fas fa-search" style="cursor: pointer;" title="Tìm kiếm"></i>
        </div>
        <div class="thong-tin-nguoi-dung">
            <span>Xin chào: <?= htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['user_email']) ?></span>
        </div>
    </header>
   <div class="noi-dung-dashboard">
    <h3>Sửa Tin tức</h3>
    <?php if (!empty($news)): ?>
        <form method="POST" action="index.php?c=news&a=sua&ma_tin_tuc=<?= $news['ma_tin_tuc'] ?>" enctype="multipart/form-data">
            <div class="form-group">
                <label for="tieu_de">Tiêu đề:</label>
                <input type="text" name="tieu_de" id="tieu_de" value="<?= htmlspecialchars($news['tieu_de']) ?>" required>
            </div>
            <div class="form-group">
                <label for="noi_dung">Nội dung:</label>
                <textarea name="noi_dung" id="noi_dung" required><?= htmlspecialchars($news['noi_dung']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="hinh_anh">Hình ảnh:</label>
                <input type="file" name="hinh_anh" id="hinh_anh" accept="image/*">
                <input type="hidden" name="hinh_anh_hien_tai" value="<?= htmlspecialchars($news['hinh_anh']) ?>">
                <img src="<?= htmlspecialchars($news['hinh_anh']) ?>" alt="Hình ảnh hiện tại" class="news-img" style="margin-top: 10px;">
            </div>
            <div class="form-group">
                <label for="trang_thai_hien_thi">Trạng thái:</label>
                <input type="checkbox" name="trang_thai_hien_thi" id="trang_thai_hien_thi" value="1" <?= $news['trang_thai_hien_thi'] ? 'checked' : '' ?>> Hiện
            </div>
            <button type="submit" class="them-moi-btn">Cập nhật</button>
        </form>
    <?php else: ?>
        <p>Tin tức không tồn tại.</p>
    <?php endif; ?>
</div>
</div>