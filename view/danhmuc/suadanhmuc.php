<?php
date_default_timezone_set('Asia/Ho_Chi_Minh'); 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Danh mục - Zamy Shop Admin</title>
    
    <!-- CSS cho giao diện dashboard -->
    <link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
    
    <!-- CSS riêng cho trang danh mục -->
    <link rel="stylesheet" href="assets/css/category.css">
    <link rel="stylesheet" href="assets/css/danhmuc.css">
    
    <!-- Font Awesome cho icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Dashboard JavaScript -->
    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/danhmuc.js"></script>
    <script src="../../assets/js/danhmuc.js"></script>
</head>
<body>
    <!-- Container chính của dashboard -->
    <div class="khung-dashboard">
        
        <!-- Include thanh menu có thể tái sử dụng -->
        <?php include __DIR__ . '/../menu.php'; ?>

        <!-- Nội Dung Chính -->
        <main class="noi-dung-chinh">
            <!-- Nội dung trang Sửa Danh mục -->
            <div class="noi-dung-trang-danh-muc">
                <div class="tieu-de-trang">
                    <h2><i class="fas fa-edit"></i> Sửa Danh mục</h2>
                    <div class="nut-hanh-dong">
                        <a href="index.php?c=danhmuc&a=index" class="nut-thao-tac nut-quay-lai">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>

                <?php if (!empty($thongBaoLoi)): ?>
                <div class="thong-bao-loi">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($thongBaoLoi) ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($thongBaoThanhCong)): ?>
                <div class="thong-bao-thanh-cong">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($thongBaoThanhCong) ?>
                </div>
                <?php endif; ?>

                <!-- Form sửa danh mục -->
                <div class="khung-form">
                    <?php if (isset($danhMuc) && !empty($danhMuc)): ?>
                    <form method="POST" action="index.php?c=danhmuc&a=update&id=<?= $danhMuc['ma_danh_muc'] ?>" class="form-sua-danh-muc">
                        <!-- Hiển thị thông tin danh mục hiện tại -->
                        

                        <div class="nhom-input">
                            <label for="ten_danh_muc" class="nhan-input">
                                <i class="fas fa-tag"></i> Tên danh mục <span class="bat-buoc">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="ten_danh_muc" 
                                name="ten_danh_muc" 
                                class="o-input"
                                placeholder="Nhập tên danh mục..." 
                                value="<?= htmlspecialchars($_POST['ten_danh_muc'] ?? $danhMuc['ten_danh_muc']) ?>"
                                required
                                maxlength="100"
                            >
                            <small class="ghi-chu">Tên danh mục phải từ 2-100 ký tự</small>
                        </div>

                        <div class="nhom-nut-hanh-dong">
                            <button type="submit" class="nut-thao-tac nut-chinh">
                                <i class="fas fa-save"></i> Cập nhật danh mục
                            </button>
                            <a href="index.php?c=danhmuc&a=index" class="nut-thao-tac nut-huy">
                                <i class="fas fa-times"></i> Hủy bỏ
                            </a>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="thong-bao-loi">
                        <i class="fas fa-exclamation-triangle"></i>
                        Không tìm thấy danh mục cần sửa!
                    </div>
                    <div class="nhom-nut-hanh-dong">
                        <a href="index.php?c=danhmuc&a=index" class="nut-thao-tac nut-quay-lai">
                            <i class="fas fa-arrow-left"></i> Quay lại danh sách
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Validation form
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.form-sua-danh-muc');
            const inputTenDanhMuc = document.getElementById('ten_danh_muc');
            
            if (form && inputTenDanhMuc) {
                form.addEventListener('submit', function(e) {
                    const tenDanhMuc = inputTenDanhMuc.value.trim();
                    
                    if (tenDanhMuc.length < 2) {
                        e.preventDefault();
                        alert('Tên danh mục phải có ít nhất 2 ký tự!');
                        inputTenDanhMuc.focus();
                        return false;
                    }
                    
                    if (tenDanhMuc.length > 100) {
                        e.preventDefault();
                        alert('Tên danh mục không được vượt quá 100 ký tự!');
                        inputTenDanhMuc.focus();
                        return false;
                    }
                    
                    // Xác nhận trước khi cập nhật
                    if (!confirm('Bạn có chắc chắn muốn cập nhật danh mục này không?')) {
                        e.preventDefault();
                        return false;
                    }
                });
                
                // Auto focus vào input và select toàn bộ text
                inputTenDanhMuc.focus();
                inputTenDanhMuc.select();
            }
        });
    </script>

</body>
</html>
