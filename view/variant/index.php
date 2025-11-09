<link rel="stylesheet" href="assets/css/variant.css">
<?php include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="assets/css/pagination.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/variant.js"></script>

<div class="noi-dung-chinh">
    <div class="thanh-tieu-de">
        <h2>Danh sách biến thể sản phẩm</h2>
        <a href="index.php?c=variant&a=add" class="them-moi-btn"><i class="fas fa-plus"></i> Thêm biến thể</a>
    </div>

    <div class="hop-tim-kiem">
        <form method="GET" action="">
            <input type="hidden" name="c" value="variant">
            <input type="hidden" name="a" value="index">
            <input type="text" name="keyword" value="<?= htmlspecialchars($keyword ?? '') ?>" placeholder="Tìm theo tên sản phẩm...">
            <button type="submit"><i class="fa fa-search"></i></button>
            <button type="button" class="reset-btn" onclick="window.location.href='index.php?c=variant&a=index'">Tải lại</button>
        </form>
    </div>

    <?php if (!empty($_GET['success'])): ?>
      <div style="background:#d4edda;color:#155724;padding:12px;border-radius:6px;margin-bottom:15px;">
        <i class="fa fa-check-circle"></i> <?= htmlspecialchars($_GET['success']) ?>
      </div>
    <?php elseif (!empty($_GET['error'])): ?>
      <div style="background:#f8d7da;color:#721c24;padding:12px;border-radius:6px;margin-bottom:15px;">
        <i class="fa fa-exclamation-circle"></i> <?= htmlspecialchars($_GET['error']) ?>
      </div>
    <?php endif; ?>

    <table class="news-list">
        <thead>
            <tr>
                <th>Mã biến thể</th>
                <th>Tên sản phẩm</th>
                <th>Màu</th>
                <th>Size</th>
                <th>Tồn kho</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($variants)): ?>
                <?php foreach ($variants as $v): ?>
                    <tr>
                        <td><?= htmlspecialchars($v['ma_bien_the']) ?></td>
                        <td><?= htmlspecialchars($v['ten_san_pham']) ?></td>
                        <td><?= htmlspecialchars($v['ten_mau']) ?></td>
                        <td><?= htmlspecialchars($v['ten_size']) ?></td>
                        <td><?= htmlspecialchars($v['ton_kho']) ?></td>
                        <td>
                            <!-- Luôn hiện Sửa -->
                            <a href="index.php?c=variant&a=edit&id=<?= $v['ma_bien_the'] ?>" class="action-link edit-link">
                                <i class="fas fa-edit"></i> Sửa
                            </a>

                            <!-- Ẩn Xóa nếu không có quyền -->
                            <?php if (can('product.crud')): ?>
                                <a href="#" class="action-link delete-link" onclick="openPopup(<?= $v['ma_bien_the'] ?>)">
                                    <i class="fas fa-trash"></i> Xóa
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center;">Không có dữ liệu</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($totalPages > 1): ?>
        <div class="user-pagination-wrapper">
            <div class="user-pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="index.php?c=variant&a=index&page=<?= $i ?>&keyword=<?= urlencode($keyword ?? '') ?>" 
                       class="<?= ($i == $page) ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Popup xác nhận xóa chỉ hiển thị nếu có quyền -->
<?php if (can('product.crud')): ?>
<div class="popup" id="popupXoa">
  <div class="popup-content">
    <h3>Xác nhận xóa</h3>
    <p>Bạn có chắc chắn muốn xóa biến thể này không?</p>
    <div class="popup-actions">
      <button id="btnXacNhanXoa" class="btn-luu">Xóa</button>
      <button type="button" onclick="closePopup()" class="btn-dong">Hủy</button>
    </div>
  </div>
</div>
<?php endif; ?>
