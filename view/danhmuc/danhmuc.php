<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Danh mục - Zamy Shop Admin</title>
    
    <!-- CSS cho giao diện dashboard -->
    <link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
    
    <!-- CSS riêng cho trang danh mục -->
    <link rel="stylesheet" href="assets/css/category.css">
    
    <!-- Font Awesome cho icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Dashboard JavaScript -->
    <script src="assets/js/dashboard.js"></script>
    
    <!-- JavaScript riêng cho trang danh mục -->
    <script src="assets/js/danhmuc.js"></script>
</head>
<body>
    <!-- Container chính của dashboard -->
    <div class="khung-dashboard">
        
        <!-- Include thanh menu có thể tái sử dụng -->
        <?php include __DIR__ . '/../menu.php'; ?>

        <!-- Nội Dung Chính -->
        <main class="noi-dung-chinh">
            <!-- Header với search và thông tin user -->
            <header class="thanh-tieu-de">
                <!-- Ô tìm kiếm -->
                <div class="hop-tim-kiem">
                    <input 
                        type="text" 
                        id="tim-kiem-danh-muc"
                        placeholder="Tìm kiếm danh mục..." 
                        aria-label="Tìm kiếm"
                        class="input-tim-kiem-live"
                    >
                    <i class="fas fa-search"></i>
                </div>
                
                <!-- Thông tin người dùng đang đăng nhập -->
                <div class="thong-tin-nguoi-dung">
                    <span>Xin chào: <?= htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['user_email']) ?></span>
                </div>
            </header>

            <!-- Nội dung trang Quản lý Danh mục -->
            <div class="noi-dung-trang-danh-muc">
                <div class="tieu-de-trang">
                    <h2> Quản Lý Danh Mục</h2>
                    <div class="nut-hanh-dong">
                        <a href="index.php?c=danhmuc&a=create" class="nut nut-chinh">
                            <i class="fas fa-plus"></i> Thêm mới
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

                <!-- Bảng danh sách danh mục từ Supabase -->
                <div class="khung-bang-du-lieu">
                    <table class="bang-du-lieu-danh-muc">
                        <thead>
                            <tr>
                                <th>Mã danh mục</th>
                                <th>Tên danh mục</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($danhSachDanhMuc)): ?>
                                <?php foreach ($danhSachDanhMuc as $danhMuc): ?>
                                <tr class="dong-du-lieu-danh-muc">
                                    <td class="ma-danh-muc"><?= htmlspecialchars($danhMuc['ma_danh_muc']) ?></td>
                                    <td class="ten-danh-muc"><?= htmlspecialchars($danhMuc['ten_danh_muc']) ?></td>
                                    <td class="ngay-tao">
                                        <?= date('d/m/Y H:i', strtotime($danhMuc['created_at'])) ?>
                                    </td>
                                    <td class="cac-nut-thao-tac">
                                        <a href="index.php?c=danhmuc&a=edit&id=<?= $danhMuc['ma_danh_muc'] ?>" 
                                           class="nut-thao-tac sua" title="Sửa danh mục">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="nut-thao-tac xoa" title="Xóa danh mục" 
                                                onclick="xoaDanhMuc(<?= $danhMuc['ma_danh_muc'] ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="dong-khong-co-du-lieu">
                                    <td colspan="4" class="thong-bao-khong-co-du-lieu">
                                        <i class="fas fa-info-circle"></i>
                                        Chưa có danh mục nào. Hãy thêm danh mục đầu tiên!
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        function xoaDanhMuc(maDanhMuc) {
            if (confirm('Bạn có chắc chắn muốn xóa danh mục này không?')) {
                // Chuyển đến trang xóa danh mục
                window.location.href = 'index.php?c=danhmuc&a=delete&id=' + maDanhMuc;
            }
        }
    </script>

</body>
</html>
</html>