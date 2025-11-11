<?php $title = "Đơn hàng đang xử lý"; 
date_default_timezone_set('Asia/Ho_Chi_Minh');
?>
<?php include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="assets/css/orders.css">
<link rel="stylesheet" href="assets/css/pagination.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/danhmuc.js"></script>
<script src="assets/js/order.js"></script>

<head>
    <meta charset="UTF-8">
    <title>Quản lý Đơn hàng - Đang xử lý</title>
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
                        <th>Ngày giao</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Nhân viên xử lý</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order):
                        if (!is_array($order)) continue;

                        // Truy cập dữ liệu an toàn
                        $maDonHang = $order['ma_don_hang'] ?? 0;
                        $statusId = $order['ma_trang_thai_don_hang'] ?? 0;
                        $statusName = $order['order_statuses']['ten_trang_thai'] ?? '—';
                        $note = $order['ghi_chu'] ?? '';
                        $canReturn = stripos($note, 'yêu cầu hoàn hàng') !== false || stripos($note, 'hoàn hàng') !== false;
                        $maNhanVien = $order['ma_nhan_vien_xu_ly'] ?? null;

                        // Xử lý ngày đặt (UTC → Việt Nam)
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

                        // Xử lý ngày giao
                        $ngayGiao = $order['ngay_giao_hang'] ?? null;
                        if ($ngayGiao) {
                            try {
                                $dtGiao = new DateTime($ngayGiao, new DateTimeZone('UTC'));
                                $dtGiao->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'));
                                $ngayGiaoFormatted = $dtGiao->format('d/m/Y H:i');
                            } catch (Exception $e) {
                                $ngayGiaoFormatted = date('d/m/Y H:i', strtotime($ngayGiao));
                            }
                        } else {
                            $ngayGiaoFormatted = '<em style="color:#999">Chưa giao</em>';
                        }

                        $tenNguoiDung = 'Khách lẻ';
                        if (isset($order['users']) && is_array($order['users']) && isset($order['users']['ten_nguoi_dung'])) {
                            $tenNguoiDung = $order['users']['ten_nguoi_dung'];
                        }
                    ?>
                    <tr data-order-id="<?= $maDonHang ?>">
                        <td>#<?= $maDonHang ?></td>
                        <td><?= htmlspecialchars($tenNguoiDung) ?></td>
                        <td><?= $ngayDatFormatted ?></td>
                        <td><?= $ngayGiaoFormatted ?></td>
                        <td><?= number_format($order['tong_gia_tri_don_hang'] ?? 0) ?>đ</td>
                        <td><span class="trang-thai status-<?= $statusId ?>"><?= $statusName ?></span></td>
                        <td>
                            <?php echo $maNhanVien ? 'ID: ' . $maNhanVien : '<em style="color:#999;">Chưa xử lý</em>'; ?>
                        </td>
                        <td>
                            <button class="nut-xem" data-id="<?= $maDonHang ?>">Xem</button>
                            <?php if ($statusId == 1): ?>
                                <button class="nut-xac-nhan" data-id="<?= $maDonHang ?>" data-action="confirm">Xác nhận</button>
                                <button class="nut-huy-don" data-id="<?= $maDonHang ?>" data-action="cancel">Hủy đơn</button>
                            <?php elseif ($statusId == 2): ?>
                                <button class="nut-giao-hang" data-id="<?= $maDonHang ?>" data-action="deliver">Giao hàng</button>
                            <?php elseif ($statusId == 3): ?>
                                <button class="nut-da-giao" data-id="<?= $maDonHang ?>" data-action="complete">Đã giao</button>
                            <?php elseif ($canReturn && in_array($statusId, [1,2,3])): ?>
                                <button class="nut-hoan-hang" data-id="<?= $maDonHang ?>" data-action="return">Chấp nhận hoàn</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($totalPages > 1): ?>
            <div class="phan-trang" style="margin-top:40px; margin-bottom:40px; text-align:center;">
                <?php
                $url = "index.php?c=order&a=processing";
                $code = trim($_GET['code'] ?? '');
                $customer = trim($_GET['customer'] ?? '');
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

<!-- POPUP CHI TIẾT -->
<div id="popup-detail" class="popup-overlay" style="display:none">
    <div class="popup-content">
        <span class="close-popup">×</span>
        <div id="popup-body"></div>
    </div>
</div>

<!-- POPUP HỦY ĐƠN -->
<div id="popup-cancel" class="popup-overlay" style="display:none">
    <div class="popup-cancel-content">
        <h3>Lý do hủy đơn</h3>
        <form id="form-huy-don">
            <input type="hidden" id="cancel-order-id" name="order_id" value="">
            <textarea id="ly-do-huy" name="ly_do_huy" placeholder="Nhập lý do hủy đơn..." required></textarea>
            <div class="popup-buttons">
                <button type="button" class="btn-cancel-cancel close-popup-cancel">Hủy</button>
                <button type="submit" class="confirm-cancel">Xác nhận hủy</button>
            </div>
        </form>
    </div>
</div>