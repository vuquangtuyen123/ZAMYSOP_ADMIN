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
        <h3>Xác nhận Xóa Tin tức</h3>
        <p>Bạn có chắc chắn muốn xóa tin tức này? Hành động này không thể hoàn tác.</p>
        <a href="index.php?c=news&a=xoa&ma_tin_tuc=<?= $_GET['ma_tin_tuc'] ?>" class="them-moi-btn" style="background-color: #dc3545;">Xác nhận xóa</a>
        <a href="index.php?c=news&a=index" class="them-moi-btn" style="background-color: #6c757d; margin-left: 10px;">Hủy</a>
    </div>
</div>