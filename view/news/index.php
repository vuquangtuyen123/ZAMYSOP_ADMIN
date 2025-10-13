<!-- Include thanh menu có thể tái sử dụng -->
<?php include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/news.css">
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/news.js"></script>
<script src="assets/js/danhmuc.js"></script>

<main class="noi-dung-chinh">
   <header class="thanh-tieu-de">
    <div class="hop-tim-kiem">
        <form method="GET" action="index.php">
            <input type="hidden" name="c" value="news">
            <input type="hidden" name="a" value="index">
            <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Tìm kiếm tin tức" aria-label="Tìm kiếm">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>
        <a href="index.php?c=news&a=index&reset=1" class="all-btn">Tất cả</a>
    </div>
    <div class="thong-tin-nguoi-dung">
        <span>Xin chào: <?= htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['user_email']) ?></span>
    </div>
</header>

    <div class="noi-dung-dashboard">
        <h3>Quản lý Tin tức</h3>
         <p>Click để hiển thị chi tiết tin tức</p>
        <a href="index.php?c=news&a=them" class="them-moi-btn"><i class="fas fa-plus"></i> Thêm mới</a>
<table class="news-list">
    <thead>
        <tr>
            <th>Mã tin tức</th>
            <th>Tiêu đề</th>
            <th>Hình ảnh</th>
            <th>Nội dung</th>
            <th>Ngày đăng</th>
            <th>Trạng thái</th>
            <th>Hành động</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!empty($news)): ?>
        <?php foreach ($news as $item): ?>
            <tr class="news-item" data-ma-tin-tuc="<?= $item['ma_tin_tuc'] ?>">
                <td><?= htmlspecialchars($item['ma_tin_tuc']) ?></td>
                <td><?= htmlspecialchars($item['tieu_de']) ?></td>
                <td><img src="<?= htmlspecialchars($item['hinh_anh']) ?>" alt="Hình ảnh tin tức" class="news-img"></td>
                <td class="news-content-cell"><?= htmlspecialchars(substr($item['noi_dung'], 0, 200)) . (strlen($item['noi_dung']) > 200 ? '...' : '') ?></td>
                <td><?= date('Y-m-d H:i:s', strtotime($item['ngay_dang'])) ?></td>
                <td>
                    <label class="switch">
                        <input type="checkbox" class="toggle-status" data-id="<?= $item['ma_tin_tuc'] ?>" <?= $item['trang_thai_hien_thi'] ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </td>
                <td>
                    <a href="index.php?c=news&a=sua&ma_tin_tuc=<?= $item['ma_tin_tuc'] ?>" class="action-link edit-link"><i class="fas fa-edit"></i> Sửa</a>
                    <a href="index.php?c=news&a=xoa&ma_tin_tuc=<?= $item['ma_tin_tuc'] ?>" class="action-link delete-link" onclick="return confirm('Bạn có chắc muốn xóa?')"><i class="fas fa-trash"></i> Xóa</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="7" style="text-align:center;">Không có tin tức nào</td></tr>
    <?php endif; ?>
</tbody>

<div class="pagination" style="margin-top:20px; text-align:center;">
    <?php if ($totalPages > 1): ?>
        <?php
            $prevPage = $page > 1 ? $page - 1 : 1;
            $nextPage = $page < $totalPages ? $page + 1 : $totalPages;
        ?>
        <a href="index.php?c=news&a=index&page=<?= $prevPage ?><?= $search ? '&search=' . urlencode($search) : '' ?>"
           style="margin:0 4px; padding:6px 12px; border-radius:4px; border:1px solid #ddd; text-decoration:none; color:#2196F3;">
            &lt;
        </a>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="index.php?c=news&a=index&page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>"
               class="<?= $i === $page ? 'active' : '' ?>"
               style="margin:0 4px; padding:6px 12px; border-radius:4px; border:1px solid #ddd; text-decoration:none;<?= $i === $page ? 'background:#2196F3;color:#fff;' : 'color:#2196F3;' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
        <a href="index.php?c=news&a=index&page=<?= $nextPage ?><?= $search ? '&search=' . urlencode($search) : '' ?>"
           style="margin:0 4px; padding:6px 12px; border-radius:4px; border:1px solid #ddd; text-decoration:none; color:#2196F3;">
            &gt;
        </a>
    <?php endif; ?>
</div>

<script>
function showToast(message) {
    let toast = document.getElementById("toast");
    if (!toast) {
        toast = document.createElement("div");
        toast.id = "toast";
        toast.className = "toast";
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.classList.add("show");
    setTimeout(() => toast.classList.remove("show"), 3000);
}
<?php if (!empty($message)): ?>
    showToast("<?= addslashes($message) ?>");
<?php endif; ?>

