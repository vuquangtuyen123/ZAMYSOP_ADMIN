<?php
include __DIR__ . '/../menu.php';
?>
<link rel="stylesheet" href="assets/css/pagination.css">
<?php
$grouped_reviews = $grouped_reviews ?? [];
$products = $products ?? [];
$total_pages = $total_pages ?? 1;
$current_page = max(1, (int)($_GET['page'] ?? 1));
$filter = $_GET['filter'] ?? 'all';
$rating = $_GET['rating'] ?? null;
$reply_status = $_GET['reply_status'] ?? null;
$tab = $_GET['tab'] ?? 'reviews';

$toast_message = $_GET['toast_message'] ?? '';
$toast_type = $_GET['toast_type'] ?? 'success';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý bình luận</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
    <link rel="stylesheet" href="assets/css/comment.css">
    <script src="assets/js/dashboard.js"></script>
<script src="assets/js/danhmuc.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div id="toast" class="toast"></div>

<div class="container">
    <h2>Quản lý bình luận</h2>
    <p>Quản lý đánh giá sản phẩm - Hệ thống tự động ẩn bình luận tiêu cực.</p>

    <div class="tabs">
        <button class="tab <?= $tab === 'reviews' ? 'active' : '' ?>" onclick="switchTab('reviews')">
            Danh sách đánh giá
        </button>
        <button class="tab <?= $tab === 'products' ? 'active' : '' ?>" onclick="switchTab('products')">
            Sản phẩm đánh giá
        </button>
    </div>

    <?php if ($tab === 'reviews'): ?>
    <!-- [PHẦN DANH SÁCH ĐÁNH GIÁ - GIỮ NGUYÊN] -->
    <div class="toolbar">
        <div class="left">
            <a href="index.php?c=comment&a=index&filter=all&page=<?= $current_page ?>"><button>Tất cả</button></a>
            <a href="index.php?c=comment&a=index&filter=positive&page=<?= $current_page ?>"><button>Bình luận tích cực</button></a>
            <div style="display:inline-block;position:relative;">
                <button onclick="toggleRating()">Điểm đánh giá</button>
                <div id="rating-menu" style="display:none;">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <a href="index.php?c=comment&a=index&rating=<?= $i ?>&page=<?= $current_page ?>"><div><?= $i ?> sao</div></a>
                    <?php endfor; ?>
                </div>
            </div>
            <a href="index.php?c=comment&a=index&reply_status=1&page=<?= $current_page ?>"><button>Đã phản hồi</button></a>
            <a href="index.php?c=comment&a=index&reply_status=0&page=<?= $current_page ?>"><button>Chưa phản hồi</button></a>
        </div>
        <div class="right">
            <a href="index.php?c=comment&a=index&filter=hidden&page=<?= $current_page ?>"><button>Xem đánh giá tiêu cực</button></a>
            <a href="index.php?c=comment&a=delete_all" onclick="return confirmDeleteAll();"><button>Xóa tất cả</button></a>
        </div>
    </div>

    <div class="page-info">Tổng <?= count($grouped_reviews) ?> đánh giá trên trang <?= $current_page ?>/<?= $total_pages ?></div>

    <table>
        <thead>
            <tr>
                <th><input type="checkbox" id="select-all" onclick="toggleSelectAll()"></th>
                <th>Điểm</th>
                <th>Nội dung</th>
                <th>Thời gian</th>
                <th>Trạng thái</th>
                <th>Phản hồi</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php
            function displayReply($reply, $level = 1) {
                global $current_page; ?>
                <div class="reply level-<?= $level ?>">
                    <div class="review-content">
                        <b style="color:#00b894;"><?= htmlspecialchars($reply['users']['ten_nguoi_dung']) ?>:</b>
                        <?= htmlspecialchars($reply['noi_dung_danh_gia']) ?>
                    </div>
                    <div class="review-info">
                        - <?= date('d/m/Y H:i', strtotime($reply['thoi_gian_tao'])) ?>
                        - <span class="status <?= $reply['trang_thai'] == 1 ? 'display' : 'hidden' ?>">
                            <?= $reply['trang_thai'] == 1 ? 'TÍCH CỰC' : 'TIÊU CỰC' ?>
                        </span>
                        - <span class="status-reply <?= $reply['trang_thai_phan_hoi'] == 1 ? 'done' : 'pending' ?>">
                            <?= $reply['trang_thai_phan_hoi'] == 1 ? 'Đã phản hồi' : 'Chưa phản hồi' ?>
                        </span>
                    </div>

                    <?php if (!empty($reply['hinh_anh'])): ?>
                        <div class="image-container">
                            <?php foreach ($reply['hinh_anh'] as $image): ?>
                                <a href="<?= htmlspecialchars($image) ?>" target="_blank">
                                    <img src="<?= htmlspecialchars($image) ?>" class="review-img" alt="Ảnh">
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="action">
                        <?php if ($reply['trang_thai'] == 1): ?>
                            <a href="index.php?c=comment&a=change_status&review_id=<?= $reply['ma_danh_gia'] ?>&status=hidden&page=<?= $current_page ?>" onclick="return confirmAction('Ẩn bình luận này?', 'Đã ẩn')">
                                <button class="btn-hide">Ẩn</button>
                            </a>
                        <?php else: ?>
                            <a href="index.php?c=comment&a=change_status&review_id=<?= $reply['ma_danh_gia'] ?>&status=display&page=<?= $current_page ?>" onclick="return confirmAction('Hiện bình luận này?', 'Đã hiện')">
                                <button class="btn-hide">Hiện</button>
                            </a>
                        <?php endif; ?>
                        <a href="index.php?c=comment&a=change_status&review_id=<?= $reply['ma_danh_gia'] ?>&status=deleted&page=<?= $current_page ?>" onclick="return confirmAction('Xóa bình luận con?', 'Đã xóa')">
                            <button class="btn-delete"><i class="fa fa-trash"></i></button>
                        </a>
                    </div>

                    <button class="toggle-replies" onclick="toggleReplyBox('<?= $reply['ma_danh_gia'] ?>')">Phản hồi</button>
                    <form class="reply-box" id="reply-<?= $reply['ma_danh_gia'] ?>" method="POST" action="index.php?c=comment&a=reply" onsubmit="return confirmAction('Gửi phản hồi?', 'Đã gửi')">
                        <input type="hidden" name="review_id" value="<?= $reply['ma_danh_gia'] ?>">
                        <input type="text" name="reply" placeholder="Nhập phản hồi..." required>
                        <button type="submit">Gửi</button>
                    </form>

                    <?php if (!empty($reply['replies']) && $reply['total_replies'] > 0): ?>
                        <button class="toggle-replies" onclick="toggleReplies('<?= $reply['ma_danh_gia'] ?>')" data-count="<?= $reply['total_replies'] ?>">
                            Xem phản hồi (<?= $reply['total_replies'] ?>)
                        </button>
                        <div id="replies-<?= $reply['ma_danh_gia'] ?>" style="display:none;">
                            <?php foreach ($reply['replies'] as $sub_reply): ?>
                                <?php if ($sub_reply['trang_thai'] == 1): displayReply($sub_reply, $level + 1); endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php } ?>

            <?php foreach ($grouped_reviews as $review): ?>
                <?php if ($review['trang_thai'] != -1): ?>
                <tr>
                    <td><input type="checkbox" class="review-checkbox"></td>
                    <td>
                        <?php if (empty($review['ma_danh_gia_cha'])): ?>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fa<?= $i <= $review['diem_danh_gia'] ? 's' : 'r' ?> fa-star stars"></i>
                            <?php endfor; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="review-content"><?= htmlspecialchars($review['noi_dung_danh_gia']) ?></div>
                        <div class="review-info">
                            - <?= htmlspecialchars($review['users']['ten_nguoi_dung']) ?> đánh giá 
                            <b><?= htmlspecialchars($review['products']['ten_san_pham']) ?></b>
                        </div>

                        <?php if (!empty($review['hinh_anh'])): ?>
                            <div class="image-container">
                                <?php foreach ($review['hinh_anh'] as $image): ?>
                                    <a href="<?= htmlspecialchars($image) ?>" target="_blank">
                                        <img src="<?= htmlspecialchars($image) ?>" class="review-img" alt="Ảnh">
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($review['trang_thai'] == 1 && !empty($review['replies']) && $review['total_replies'] > 0): ?>
                            <button class="toggle-replies" onclick="toggleReplies('<?= $review['ma_danh_gia'] ?>')" data-count="<?= $review['total_replies'] ?>">
                                Xem phản hồi (<?= $review['total_replies'] ?>)
                            </button>
                            <div id="replies-<?= $review['ma_danh_gia'] ?>" style="display:none;">
                                <?php foreach ($review['replies'] as $rep): ?>
                                    <?php if ($rep['trang_thai'] == 1): displayReply($rep); endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($review['trang_thai'] == 1): ?>
                            <button class="toggle-replies" onclick="toggleReplyBox('<?= $review['ma_danh_gia'] ?>')">Phản hồi</button>
                            <form class="reply-box" id="reply-<?= $review['ma_danh_gia'] ?>" method="POST" action="index.php?c=comment&a=reply" onsubmit="return confirmAction('Gửi phản hồi?', 'Đã gửi')">
                                <input type="hidden" name="review_id" value="<?= $review['ma_danh_gia'] ?>">
                                <input type="text" name="reply" placeholder="Nhập phản hồi..." required>
                                <button type="submit">Gửi</button>
                            </form>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($review['thoi_gian_tao'])) ?></td>
                    <td><span class="status <?= $review['trang_thai'] == 1 ? 'display' : 'hidden' ?>">
                        <?= $review['trang_thai'] == 1 ? 'TÍCH CỰC' : 'TIÊU CỰC' ?>
                    </span></td>
                    <td><span class="status-reply <?= $review['trang_thai_phan_hoi'] == 1 ? 'done' : 'pending' ?>">
                        <?= $review['trang_thai_phan_hoi'] == 1 ? 'Đã phản hồi' : 'Chưa phản hồi' ?>
                    </span></td>
                    <td class="action">
                        <?php if ($review['trang_thai'] == 1): ?>
                            <a href="index.php?c=comment&a=change_status&review_id=<?= $review['ma_danh_gia'] ?>&status=hidden&page=<?= $current_page ?>" onclick="return confirmAction('Ẩn bình luận này?', 'Đã ẩn')">
                                <button class="btn-hide">Ẩn</button>
                            </a>
                        <?php else: ?>
                            <a href="index.php?c=comment&a=change_status&review_id=<?= $review['ma_danh_gia'] ?>&status=display&page=<?= $current_page ?>" onclick="return confirmAction('Hiện bình luận này?', 'Đã hiện')">
                                <button class="btn-hide">Hiện</button>
                            </a>
                        <?php endif; ?>
                        <a href="index.php?c=comment&a=change_status&review_id=<?= $review['ma_danh_gia'] ?>&status=deleted&page=<?= $current_page ?>" onclick="return confirmAction('Xóa đánh giá này và tất cả phản hồi con?', 'Đã xóa')">
                            <button class="btn-delete"><i class="fa fa-trash"></i></button>
                        </a>
                    </td>
                </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="index.php?c=comment&a=index&filter=<?= $filter ?>&rating=<?= $rating ?>&reply_status=<?= $reply_status ?>&page=<?= $i ?>" class="<?= $i == $current_page ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>

    <?php else: ?>
    <h3>Thống kê đánh giá sản phẩm</h3>

    <!-- 2 BIỂU ĐỒ NGANG HÀNG -->
    <div class="chart-container">
        <div class="chart-item">
            <canvas id="reviewCountChart"></canvas>
        </div>
        <div class="chart-item">
            <canvas id="sentimentChart"></canvas>
        </div>
    </div>

    <div style="margin-top: 30px;">
        <h4>Top 5 sản phẩm có nhiều đánh giá tiêu cực nhất</h4>
        <?php if (!empty($top_negative)): ?>
            <ul style="list-style: none; padding: 0;">
                <?php foreach ($top_negative as $p): ?>
                    <li style="margin: 8px 0; padding: 10px; background: #fff; border-left: 4px solid #e74c3c;">
                        <strong><?= htmlspecialchars($p['ten_san_pham']) ?></strong>: 
                        <span style="color: #e74c3c;"><?= $p['tieu_cuc'] ?> tiêu cực</span>
                        (Tổng: <?= $p['so_luong'] ?>)
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Không có đánh giá tiêu cực nào.</p>
        <?php endif; ?>
    </div>

    <!-- BẢNG SẢN PHẨM - HIỂN THỊ SỐ + ICON SAO -->
    <table style="margin-top: 20px;">
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Số lượt</th>
                <th>Điểm TB</th>
                <th>Tích cực</th>
                <th>Tiêu cực</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['ten_san_pham']) ?></td>
                    <td><?= $p['so_luong'] ?></td>
                    <td class="avg-rating">
                        <?= number_format($p['sao_tb'], 1) ?>
                        <span class="stars">
                            <?php
                            $full = floor($p['sao_tb']);
                            $half = ($p['sao_tb'] - $full >= 0.5) ? 1 : 0;
                            for ($i = 1; $i <= 5; $i++):
                                if ($i <= $full) echo '<i class="fas fa-star"></i>';
                                elseif ($i == $full + 1 && $half) echo '<i class="fas fa-star-half-alt"></i>';
                                else echo '<i class="far fa-star"></i>';
                            endfor;
                            ?>
                        </span>
                    </td>
                    <td style="color: #27ae60;"><?= $p['tich_cuc'] ?></td>
                    <td style="color: #e74c3c;"><?= $p['tieu_cuc'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top:15px;">
        <a href="index.php?c=comment&a=index&tab=reviews"><button class="back-button">Quay lại</button></a>
    </div>
    <?php endif; ?>
</div>

<script>
const currentPage = <?= $current_page ?>;

<?php if (!empty($toast_message)): ?>
    showToast(<?= json_encode(htmlspecialchars_decode($toast_message)) ?>, '<?= $toast_type ?>');
<?php endif; ?>

<?php if ($tab === 'products'): ?>
new Chart(document.getElementById('reviewCountChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($chart_product_names) ?>,
        datasets: [{
            label: 'Số lượng đánh giá',
            data: <?= json_encode($chart_review_counts) ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { 
            legend: { position: 'top' }, 
            title: { display: true, text: 'Số lượng đánh giá theo sản phẩm', font: { size: 16 } } 
        },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

new Chart(document.getElementById('sentimentChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: ['Tích cực', 'Tiêu cực'],
        datasets: [{
            data: [<?= $total_positive ?>, <?= $total_negative ?>],
            backgroundColor: ['#27ae60', '#e74c3c'],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { 
            legend: { position: 'bottom' }, 
            title: { display: true, text: 'Tỷ lệ cảm xúc toàn hệ thống', font: { size: 16 } } 
        }
    }
});
<?php endif; ?>
</script>

<script src="assets/js/comment.js"></script>
</body>
</html>