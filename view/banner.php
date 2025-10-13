<?php include 'view/layouts/header.php'; ?>
<div class="noi-dung-chinh">
    <header class="thanh-tieu-de">
        <div class="hop-tim-kiem">
            <input type="text" placeholder="Tìm kiếm banner" aria-label="Tìm kiếm banner">
            <i class="fas fa-search" style="cursor: pointer;" title="Tìm kiếm"></i>
        </div>
        <div class="thong-tin-nguoi-dung">
            <span>Xin chào: <?= htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['user_email']) ?></span>
        </div>
    </header>
    <div class="noi-dung-dashboard">
        <h3>Quản lý Banner</h3>
        <a href="index.php?c=banner&a=create" class="them-moi">Thêm mới</a>
        <table>
            <thead>
                <tr>
                    <th>Mã banner</th>
                    <th>Hình ảnh</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($banners as $item): ?>
                    <tr>
                        <td><?= $item['ma_banner'] ?></td>
                        <td><img src="<?= $item['hinh_anh'] ?>" alt="Banner" style="width: 100px;"></td>
                        <td><?= $item['trang_thai'] ? 'Hiện' : 'Ẩn' ?></td>
                        <td><?= $item['ngay_tao'] ?></td>
                        <td>
                            <a href="index.php?c=banner&a=edit&ma_banner=<?= $item['ma_banner'] ?>">Sửa</a>
                            <a href="index.php?c=banner&a=delete&ma_banner=<?= $item['ma_banner'] ?>" onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
