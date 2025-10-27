<?php
include __DIR__ . '/../menu.php';
$grouped_reviews = $grouped_reviews ?? [];
$products = $products ?? [];
$total_pages = isset($total_pages) ? $total_pages : 1;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$filter = $_GET['filter'] ?? 'all';
$rating = $_GET['rating'] ?? null;
$reply_status = $_GET['reply_status'] ?? null;

// Kiểm tra thông báo từ backend (nếu có)
$toast_message = isset($_GET['toast_message']) ? htmlspecialchars($_GET['toast_message']) : '';
$toast_type = isset($_GET['toast_type']) ? htmlspecialchars($_GET['toast_type']) : 'success';
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
</head>
<body>
<!-- Toast notification -->
<div id="toast" class="toast"></div>

<div class="container">
    <h2>Quản lý bình luận</h2>
    <p>Quản lý đánh giá sản phẩm - Hệ thống tự động ẩn bình luận tiêu cực.</p>

    <!-- Tabs -->
    <div class="tabs">
        <button class="tab <?php echo (!isset($_GET['tab']) || $_GET['tab'] == 'reviews') ? 'active' : ''; ?>" onclick="switchTab('reviews')">Danh sách đánh giá</button>
        <button class="tab <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'products') ? 'active' : ''; ?>" onclick="switchTab('products')">Sản phẩm đánh giá</button>
    </div>

    <!-- Toolbar -->
    <?php if (!isset($_GET['tab']) || $_GET['tab'] == 'reviews'): ?>
    <div class="toolbar">
        <div class="left">
            <a href="index.php?c=comment&a=index&filter=all&page=<?php echo $current_page; ?>"><button>Tất cả</button></a>
            <div style="display:inline-block;position:relative;">
                <button onclick="toggleRating()">Điểm đánh giá ▼</button>
                <div id="rating-menu">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <a href="index.php?c=comment&a=index&rating=<?php echo $i; ?>&page=<?php echo $current_page; ?>"><div><?php echo $i; ?> sao</div></a>
                    <?php endfor; ?>
                </div>
            </div>
            <a href="index.php?c=comment&a=index&reply_status=1&page=<?php echo $current_page; ?>"><button>Đã phản hồi</button></a>
            <a href="index.php?c=comment&a=index&reply_status=0&page=<?php echo $current_page; ?>"><button>Chưa phản hồi</button></a>
        </div>
        <div class="right">
            <a href="index.php?c=comment&a=index&filter=hidden&page=<?php echo $current_page; ?>"><button>Xem đánh giá tiêu cực</button></a>
            <a href="index.php?c=comment&a=delete_all&page=<?php echo $current_page; ?>" onclick="return confirmDeleteAll();"><button>Xóa tất cả</button></a>
        </div>
    </div>

    <div class="page-info">Tổng <?php echo count($grouped_reviews); ?> đánh giá trên trang <?php echo $current_page; ?>/<?php echo $total_pages; ?> trang</div>

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
                global $current_page;
                $levelClass = "reply level-$level";
            ?>
                <div class="<?php echo $levelClass; ?>">
                    <div class="review-content">
                        <b style="color:#00b894;"><?php echo htmlspecialchars($reply['users']['ten_nguoi_dung']); ?>:</b>
                        <?php echo htmlspecialchars($reply['noi_dung_danh_gia']); ?>
                    </div>
                    <div class="review-info">
                        - <?php echo date('d/m/Y H:i', strtotime($reply['thoi_gian_tao'])); ?>
                        - <span class="status <?php echo $reply['trang_thai'] == 1 ? 'display' : 'hidden'; ?>">
                            <?php echo $reply['trang_thai'] == 1 ? 'TÍCH CỰC' : 'TIÊU CỰC'; ?>
                        </span>
                        - <span class="status-reply <?php echo $reply['trang_thai_phan_hoi'] == 1 ? 'done' : 'pending'; ?>">
                            <?php echo $reply['trang_thai_phan_hoi'] == 1 ? 'Đã phản hồi' : 'Chưa phản hồi'; ?>
                        </span>
                    </div>

                    <?php if (!empty($reply['hinh_anh'])): ?>
                        <div class="image-container">
                            <?php foreach ($reply['hinh_anh'] as $image): ?>
                                <a href="<?php echo htmlspecialchars($image); ?>" target="_blank">
                                    <img src="<?php echo htmlspecialchars($image); ?>" class="review-img" alt="Ảnh phản hồi">
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Thao tác cho bình luận con -->
                    <div class="action">
                        <?php if ($reply['trang_thai'] == 1): ?>
                            <a href="index.php?c=comment&a=change_status&review_id=<?php echo $reply['ma_danh_gia']; ?>&status=hidden&page=<?php echo $current_page; ?>" onclick="return confirmAction('Ẩn bình luận này?', 'Đã ẩn bình luận');"><button class="btn-hide">Ẩn</button></a>
                        <?php else: ?>
                            <a href="index.php?c=comment&a=change_status&review_id=<?php echo $reply['ma_danh_gia']; ?>&status=display&page=<?php echo $current_page; ?>" onclick="return confirmAction('Hiện bình luận này?', 'Đã hiện bình luận');"><button class="btn-hide">Hiện</button></a>
                        <?php endif; ?>
                        <a href="index.php?c=comment&a=change_status&review_id=<?php echo $reply['ma_danh_gia']; ?>&status=deleted&page=<?php echo $current_page; ?>" onclick="return confirmAction('Xóa bình luận con?', 'Đã xóa bình luận');"><button class="btn-delete"><i class="fa fa-trash"></i></button></a>
                    </div>

                    <!-- Form phản hồi -->
                    <button class="toggle-replies" onclick="toggleReplyBox('<?php echo $reply['ma_danh_gia']; ?>')">Phản hồi</button>
                    <form class="reply-box" id="reply-<?php echo $reply['ma_danh_gia']; ?>" method="POST" action="index.php?c=comment&a=reply" onsubmit="return confirmAction('Gửi phản hồi?', 'Đã gửi phản hồi');">
                        <input type="hidden" name="review_id" value="<?php echo $reply['ma_danh_gia']; ?>">
                        <input type="text" name="reply" placeholder="Nhập phản hồi..." required>
                        <button type="submit">Gửi</button>
                    </form>

                    <!-- Các phản hồi con cấp sâu hơn -->
                    <?php if (!empty($reply['replies']) && $reply['total_replies'] > 0): ?>
                        <button class="toggle-replies" onclick="toggleReplies('<?php echo $reply['ma_danh_gia']; ?>')" data-count="<?php echo $reply['total_replies']; ?>">Xem phản hồi (<?php echo $reply['total_replies']; ?>)</button>
                        <div id="replies-<?php echo $reply['ma_danh_gia']; ?>" style="display:none;">
                            <?php foreach ($reply['replies'] as $sub_reply): ?>
                                <?php if ($sub_reply['trang_thai'] == 1): ?>
                                    <?php displayReply($sub_reply, $level + 1); ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php } ?>

            <?php foreach ($grouped_reviews as $review): ?>
                <?php if ($review['trang_thai'] != -1): // Chỉ hiển thị bình luận chưa bị xóa ?>
                <tr>
                    <td><input type="checkbox" class="review-checkbox"></td>
                    <td>
                        <?php if (empty($review['ma_danh_gia_cha'])): ?>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fa<?php echo $i <= $review['diem_danh_gia'] ? 's' : 'r'; ?> fa-star stars"></i>
                            <?php endfor; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="review-content"><?php echo htmlspecialchars($review['noi_dung_danh_gia']); ?></div>
                        <div class="review-info">
                            - <?php echo htmlspecialchars($review['users']['ten_nguoi_dung']); ?> đánh giá 
                            <b><?php echo htmlspecialchars($review['products']['ten_san_pham']); ?></b>
                        </div>

                        <?php if (!empty($review['hinh_anh'])): ?>
                            <div class="image-container">
                                <?php foreach ($review['hinh_anh'] as $image): ?>
                                    <a href="<?php echo htmlspecialchars($image); ?>" target="_blank">
                                        <img src="<?php echo htmlspecialchars($image); ?>" class="review-img" alt="Ảnh đánh giá">
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Các phản hồi con -->
                        <?php if ($review['trang_thai'] == 1 && !empty($review['replies']) && $review['total_replies'] > 0): ?>
                            <button class="toggle-replies" onclick="toggleReplies('<?php echo $review['ma_danh_gia']; ?>')" data-count="<?php echo $review['total_replies']; ?>">Xem phản hồi (<?php echo $review['total_replies']; ?>)</button>
                            <div id="replies-<?php echo $review['ma_danh_gia']; ?>" style="display:none;">
                                <?php foreach ($review['replies'] as $rep): ?>
                                    <?php if ($rep['trang_thai'] == 1): ?>
                                        <?php displayReply($rep); ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Form phản hồi cho bình luận cha -->
                        <?php if ($review['trang_thai'] == 1): ?>
                            <button class="toggle-replies" onclick="toggleReplyBox('<?php echo $review['ma_danh_gia']; ?>')">Phản hồi</button>
                            <form class="reply-box" id="reply-<?php echo $review['ma_danh_gia']; ?>" method="POST" action="index.php?c=comment&a=reply" onsubmit="return confirmAction('Gửi phản hồi?', 'Đã gửi phản hồi');">
                                <input type="hidden" name="review_id" value="<?php echo $review['ma_danh_gia']; ?>">
                                <input type="text" name="reply" placeholder="Nhập phản hồi..." required>
                                <button type="submit">Gửi</button>
                            </form>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('d/m/Y H:i', strtotime($review['thoi_gian_tao'])); ?></td>
                    <td>
                        <span class="status <?php echo $review['trang_thai'] == 1 ? 'display' : 'hidden'; ?>">
                            <?php echo $review['trang_thai'] == 1 ? 'TÍCH CỰC' : 'TIÊU CỰC'; ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-reply <?php echo $review['trang_thai_phan_hoi'] == 1 ? 'done' : 'pending'; ?>">
                            <?php echo $review['trang_thai_phan_hoi'] == 1 ? 'Đã phản hồi' : 'Chưa phản hồi'; ?>
                        </span>
                    </td>
                    <td class="action">
                        <?php if ($review['trang_thai'] == 1): ?>
                            <a href="index.php?c=comment&a=change_status&review_id=<?php echo $review['ma_danh_gia']; ?>&status=hidden&page=<?php echo $current_page; ?>" onclick="return confirmAction('Ẩn bình luận này?', 'Đã ẩn bình luận');"><button class="btn-hide">Ẩn</button></a>
                        <?php else: ?>
                            <a href="index.php?c=comment&a=change_status&review_id=<?php echo $review['ma_danh_gia']; ?>&status=display&page=<?php echo $current_page; ?>" onclick="return confirmAction('Hiện bình luận này?', 'Đã hiện bình luận');"><button class="btn-hide">Hiện</button></a>
                        <?php endif; ?>
                        <a href="index.php?c=comment&a=change_status&review_id=<?php echo $review['ma_danh_gia']; ?>&status=deleted&page=<?php echo $current_page; ?>" onclick="return confirmAction('Xóa đánh giá này và tất cả phản hồi con?', 'Đã xóa bình luận');"><button class="btn-delete"><i class="fa fa-trash"></i></button></a>
                    </td>
                </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Phân trang -->
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="index.php?c=comment&a=index&filter=<?php echo htmlspecialchars($filter); ?>&rating=<?php echo htmlspecialchars($rating); ?>&reply_status=<?php echo htmlspecialchars($reply_status); ?>&page=<?php echo $i; ?>" class="<?php echo $i == $current_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
    <?php else: ?>
    <!-- TAB SẢN PHẨM -->
    <table>
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Số lượt</th>
                <th>Điểm trung bình</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['ten_san_pham']); ?></td>
                    <td><?php echo $p['so_luong']; ?></td>
                    <td><?php echo number_format($p['sao_tb'], 1); ?> ★</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div style="margin-top:15px;">
        <a href="index.php?c=comment&a=index&tab=reviews&page=<?php echo $current_page; ?>"><button class="back-button">← Quay lại</button></a>
    </div>
    <?php endif; ?>
</div>

<script>
    const currentPage = <?php echo json_encode($current_page); ?>;
    <?php if (!empty($toast_message)): ?>
        showToast(<?php echo json_encode($toast_message); ?>, '<?php echo $toast_type; ?>');
    <?php endif; ?>
</script>

<script src="assets/js/comment.js"></script>
</body>
</html>