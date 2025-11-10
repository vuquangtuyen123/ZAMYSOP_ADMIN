<?php include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/color.css">
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="assets/css/pagination.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/color.js"></script>
<head>
    <meta charset="UTF-8">
    <title>Quản lý Màu sắc - ZamyShop</title>
</head>
<main class="noi-dung-chinh">
    <header class="thanh-tieu-de">
        <div></div>
        <div><a href="index.php?c=color&a=them" class="them-moi-btn"><i class="fas fa-plus"></i> Thêm màu</a></div>
    </header>
    <div class="noi-dung-dashboard">
        <table class="news-list">
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Tên màu</th>
                    <th>Mã HEX</th>
                    <th>Xem</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach (($colors ?? []) as $c): ?>
                <tr>
                    <td><?= (int)$c['ma_mau'] ?></td>
                    <td><?= htmlspecialchars($c['ten_mau']) ?></td>
                    <td><?= htmlspecialchars($c['ma_mau_hex']) ?></td>
                    <td>
                        <span style="display:inline-block;width:20px;height:20px;border-radius:4px;border:1px solid #ccc;background:<?= htmlspecialchars($c['ma_mau_hex']) ?>;"></span>
                    </td>
                    <td>
                        <a class="action-link edit-link" href="index.php?c=color&a=sua&id=<?= (int)$c['ma_mau'] ?>">Sửa</a>
                        <?php if (can('product.crud')): ?> <!-- Chỉ Admin mới thấy nút Xóa -->
                            <a class="action-link delete-link" href="index.php?c=color&a=xoa&id=<?= (int)$c['ma_mau'] ?>" onclick="return confirm('Xóa màu này?')">Xóa</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php if (isset($totalPages) && $totalPages > 1): ?>
                <?php 
                    $prevPage = $page > 1 ? $page - 1 : 1; 
                    $nextPage = $page < $totalPages ? $page + 1 : $totalPages; 
                ?>
                <a href="index.php?c=color&a=index&page=<?= $prevPage ?>" class="<?= $page <= 1 ? 'disabled' : '' ?>">&lt;</a>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="index.php?c=color&a=index&page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <a href="index.php?c=color&a=index&page=<?= $nextPage ?>" class="<?= $page >= $totalPages ? 'disabled' : '' ?>">&gt;</a>
            <?php endif; ?>
        </div>
    </div>
</main>
