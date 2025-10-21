<?php

error_reporting(E_ALL & ~E_NOTICE);
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');
?>

<?php include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="assets/css/message.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/danhmuc.js"></script>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Tin nhắn</title>
</head>
<body>
<div class="khung-messenger">

    <!--Cột bên trái: Danh sách user đã chat -->
    <div class="ben-trai">
        <h2>Danh sách Chat</h2>
        <div class="hop-tim-kiem">
            <form method="GET" action="index.php" id="searchForm">
                <input type="hidden" name="c" value="message">
                <input type="hidden" name="a" value="index">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($filter ?? 'all') ?>">
                <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Tìm kiếm người dùng..." id="searchInput" onkeypress="if(event.key === 'Enter') submitSearch(event)">
                <button type="submit" onclick="submitSearch(event)"></i></button>
            </form>
            <button onclick="reloadPage()" class="nut-tai-lai">Tải lại</button>
        </div>

        <div class="loc">
            <a href="index.php?c=message&a=index&filter=all&search=<?= urlencode($search ?? '') ?>">Tất cả (<?= count($chats) ?>)</a> |
            <a href="index.php?c=message&a=index&filter=unread&search=<?= urlencode($search ?? '') ?>">Chưa đọc (<?= $total_unread ?? 0 ?>)</a> |
            <a href="index.php?c=message&a=index&filter=read&search=<?= urlencode($search ?? '') ?>">Đã đọc (<?= $total_read ?? 0 ?>)</a>
        </div>

        <div class="danh-sach-chat-container">
            <?php if ($noResults): ?>
                <p class="khong-co-ket-qua">Không có kết quả phù hợp</p>
            <?php elseif (empty($chats)): ?>
                <p class="khong-co-ket-qua">Không có chat nào</p>
            <?php else: ?>
                <?php foreach ($chats as $chat): ?>
                    <div class="item-chat <?= ($chat['unread_count'] ?? 0) > 0 ? 'chua-doc' : '' ?>"
                         onclick="location.href='index.php?c=message&a=index&user_id=<?= $chat['user_id'] ?>&filter=<?= $filter ?? 'all' ?>&search=<?= urlencode($search ?? '') ?>'">
                        <?php if (!empty($chat['avatar'])): ?>
                            <img src="<?= htmlspecialchars($chat['avatar']) ?>" alt="Avatar" class="hinh-avatar">
                        <?php else: ?>
                            <div class="icon-avatar"><i class="fas fa-user"></i></div>
                        <?php endif; ?>

                        <div class="thong-tin-chat">
                            <strong><?= htmlspecialchars($chat['user_name']) ?></strong><br>
                            <?php if (!empty($chat['last_message'])): ?>
                                <small><?= htmlspecialchars($chat['last_message']['noi_dung']) ?></small><br>
                            <?php endif; ?>
                            <small>Cập nhật: <?= date('d.m.Y H:i', strtotime($chat['ngay_cap_nhat'])) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- 🟦 Cột bên phải: Chi tiết tin nhắn -->
    <div class="ben-phai">
        <?php if ($noResults): ?>
            <p class="khong-co-ket-qua">Không có kết quả phù hợp</p>

        <?php elseif (!empty($user_name)): ?>
            <h2>Chat với <?= htmlspecialchars($user_name) ?></h2>

            <div class="khung-tin-nhan">
                <?php if (!empty($messages)): ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="tin-nhan <?= ($msg['ma_nguoi_gui'] == 1) ? 'admin' : 'nguoi-dung' ?>">
                            <?php if (!empty($msg['users']['avatar'])): ?>
                                <img src="<?= htmlspecialchars($msg['users']['avatar']) ?>" alt="Avatar" class="hinh-avatar">
                            <?php else: ?>
                                <div class="icon-avatar"><i class="fas fa-user"></i></div>
                            <?php endif; ?>

                            <div class="noi-dung-tin-nhan">
                                <strong><?= htmlspecialchars($msg['users']['ten_nguoi_dung'] ?? ($msg['ma_nguoi_gui']==1 ? 'Admin' : 'User')) ?>:</strong>
                                <?= htmlspecialchars($msg['noi_dung']) ?><br>
                                <small><?= date('d.m.Y H:i', strtotime($msg['thoi_gian_gui'])) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Chưa có tin nhắn nào.</p>
                <?php endif; ?>
            </div>

            <div class="form-gui-tin">
                <form method="POST" action="index.php?c=message&a=send&filter=<?= $filter ?? 'all' ?>&search=<?= urlencode($search ?? '') ?>" onsubmit="return validateForm()">
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id ?? '') ?>">
                    <textarea name="noi_dung" id="noi_dung" placeholder="Nhập tin nhắn..." required></textarea>
                    <button type="submit" class="nut-gui"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>

        <?php else: ?>
            <p>Chọn một cuộc trò chuyện để xem chi tiết.</p>
        <?php endif; ?>
    </div>
</div>

<script>
function submitSearch(event) {
    event.preventDefault();
    const form = document.getElementById('searchForm');
    const searchValue = document.getElementById('searchInput').value.trim();
    form.submit();
}

function reloadPage() {
    window.location.href = 'index.php?c=message&a=index&filter=<?= $filter ?? 'all' ?>&search=<?= urlencode($search ?? '') ?>';
}

function validateForm() {
    const noiDung = document.getElementById('noi_dung').value.trim();
    if (noiDung === '') {
        alert('Vui lòng nhập nội dung tin nhắn!');
        return false;
    }
    return true;
}

// Ngăn gửi form lặp lại khi reload
if (window.history.replaceState) {
    window.history.replaceState(null, document.title, window.location.href);
}
</script>
</body>
</html>
