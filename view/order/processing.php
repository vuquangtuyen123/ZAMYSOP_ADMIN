<?php $title = "Đơn hàng đang xử lý"; 
// Set default timezone to Vietnam (useful for any other date() calls)
date_default_timezone_set('Asia/Ho_Chi_Minh');
?>
<head>
    <meta charset="UTF-8">
    <title>Quản lý Đơn hàng</title>
</head>
<div class="noi-dung-chinh">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <h2 style="margin:0;">Đơn hàng đang xử lý</h2>
        <button onclick="location.reload()" class="nut-tai-lai">Tải lại</button>
    </div>

    <!-- Tìm kiếm -->
    <div class="tim-kiem-don-hang">
        <form method="GET" action="index.php">
            <input type="hidden" name="c" value="order">
            <input type="hidden" name="a" value="processing">
            <div class="nhom-input">
                <input type="text" name="code" placeholder="Mã đơn" value="<?= htmlspecialchars($_GET['code'] ?? '') ?>">
                <input type="text" name="customer" placeholder="Tên khách" value="<?= htmlspecialchars($_GET['customer'] ?? '') ?>">
                <button type="submit" class="nut-tim-kiem">Tìm</button>
            </div>
        </form>
    </div>

    <!-- Bảng -->
    <?php if (empty($orders)): ?>
        <p>Không có đơn hàng nào đang xử lý.</p>
    <?php else: ?>
        <div class="bang-du-lieu">
            <table>
                <thead>
                    <tr>
                        <th>Mã ĐH</th>
                        <th>Khách hàng</th>
                        <th>Ngày đặt</th>
                        <th>Ngày giao</th> <!-- THÊM CỘT MỚI -->
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): 
                        $statusId = $order['ma_trang_thai_don_hang'];
                        $statusName = $order['order_statuses']['ten_trang_thai'] ?? '—';
                        $note = $order['ghi_chu'] ?? '';
                        $canReturn = stripos($note, 'yêu cầu hoàn hàng') !== false || stripos($note, 'hoàn hàng') !== false;

                        // CHUYỂN GMT/UTC → VIỆT NAM (GMT+7) an toàn
                        // 1) Ngày giao (có thể null)
                        $ngayGiao = $order['ngay_giao_hang'] ?? null;
                        if ($ngayGiao) {
                            try {
                               $dtGiao = new DateTime($ngayGiao, new DateTimeZone('UTC'));
                               $dtGiao->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'));
                            $ngayGiaoFormatted = $dtGiao->format('d/m/Y H:i');

                            } catch (Exception $e) {
                                // fallback nếu parse lỗi
                                $ngayGiaoFormatted = date('d/m/Y H:i', strtotime($ngayGiao));
                            }
                        } else {
                            $ngayGiaoFormatted = '<em style="color:#999">Chưa giao</em>';
                        }

                        // 2) Ngày đặt (thường không null) — parse tương tự để đảm bảo đúng timezone
                        $ngayDat = $order['ngay_dat_hang'] ?? null;
                        if ($ngayDat) {
                            try {
                                $dtDat = new DateTime($ngayDat, new DateTimeZone('UTC'));
                                $dtDat->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'));
                                $ngayDatFormatted = $dtDat->format('d/m/Y H:i');
                            } catch (Exception $e) {
                                $ngayDatFormatted = date('d/m/Y H:i', strtotime($ngayDat));
                            }
                        } else {
                            $ngayDatFormatted = '<em style="color:#999">—</em>';
                        }
                    ?>
                    <tr data-order-id="<?= $order['ma_don_hang'] ?>">
                        <td>#<?= $order['ma_don_hang'] ?></td>
                        <td><?= htmlspecialchars($order['users']['ten_nguoi_dung'] ?? 'Khách lẻ') ?></td>
                        <td><?= $ngayDatFormatted ?></td>
                        <td><?= $ngayGiaoFormatted ?></td> <!-- HIỂN THỊ NGÀY GIAO -->
                        <td><?= number_format($order['tong_gia_tri_don_hang']) ?>đ</td>
                        <td><span class="trang-thai status-<?= $statusId ?>"><?= $statusName ?></span></td>
                        <td>
                            <button class="nut-xem" data-id="<?= $order['ma_don_hang'] ?>">Xem</button>
                            <?php if ($statusId == 1): ?>
                                <button class="nut-xac-nhan" data-id="<?= $order['ma_don_hang'] ?>" data-action="confirm">Xác nhận</button>
                            <?php elseif ($statusId == 2): ?>
                                <button class="nut-giao-hang" data-id="<?= $order['ma_don_hang'] ?>" data-action="deliver">Giao hàng</button>
                            <?php elseif ($statusId == 3): ?>
                                <button class="nut-da-giao" data-id="<?= $order['ma_don_hang'] ?>" data-action="complete">Đã giao</button>
                            <?php elseif ($canReturn && in_array($statusId, [1,2,3])): ?>
                                <button class="nut-hoan-hang" data-id="<?= $order['ma_don_hang'] ?>" data-action="return">Chấp nhận hoàn</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($totalPages > 1): ?>
            <div class="phan-trang">
                <?php
                $url = "index.php?c=order&a=processing";
                $url .= $code ? "&code=" . urlencode($code) : '';
                $url .= $customer ? "&customer=" . urlencode($customer) : '';
                ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="<?= $url ?>&page=<?= $i ?>" class="<?= $i == $page ? 'trang-hien-tai' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- POPUP -->
<div id="popup-detail" class="popup-overlay" style="display:none">
    <div class="popup-content">
        <span class="close-popup">×</span>
        <div id="popup-body"></div>
    </div>
</div>

<!-- TÁCH JS + CSS -->
<link rel="stylesheet" href="assets/css/orders.css">
<script src="assets/js/order.js"></script>
<?php include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/danhmuc.js"></script>
