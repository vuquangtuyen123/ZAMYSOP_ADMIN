<div class="modal-content">
    <span class="close-modal">&times;</span>
    <h3>Chi tiết Tin tức</h3>
    <?php
    $ma_tin_tuc = $_GET['ma_tin_tuc'] ?? '';
    $news = (new NewsModel())->getNewsById($ma_tin_tuc);
    if (!empty($news)): ?>
        <p><strong>Mã tin tức:</strong> <?= htmlspecialchars($news['ma_tin_tuc']) ?></p>
        <p><strong>Tiêu đề:</strong> <?= htmlspecialchars($news['tieu_de']) ?></p>
        <p><strong>Nội dung:</strong> <?= htmlspecialchars($news['noi_dung']) ?></p>
        <p><strong>Hình ảnh:</strong> <img src="<?= htmlspecialchars($news['hinh_anh']) ?>" alt="Hình ảnh" class="news-img" style="max-width: 300px;"></p>
        <p><strong>Ngày đăng:</strong> <?= date('Y-m-d H:i', strtotime($news['ngay_dang'])) ?></p>
        <p><strong>Trạng thái:</strong> <?= $news['trang_thai_hien_thi'] ? 'Hiện' : 'Ẩn' ?></p>
    <?php else: ?>
        <p>Tin tức không tồn tại.</p>
    <?php endif; ?>
</div>