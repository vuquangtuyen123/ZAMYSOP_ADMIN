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
    <title>Quản lý Đơn hàng</title>
</head>
<div class="noi-dung-chinh">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <h2 style="margin:0;">Đơn hàng đã giao</h2>
        <button onclick="location.reload()" class="nut-tai-lai">Tải lại</button>
    </div>

    <!-- Tìm kiếm -->
    <div class="tim-kiem-don-hang">
        <form method="GET" action="index.php">
            <input type="hidden" name="c" value="order">
            <input type="hidden" name="a" value="completed">
            <div class="nhom-input">
                <input type="text" name="code" placeholder="Mã đơn" value="<?= htmlspecialchars($_GET['code'] ?? '') ?>">
                <input type="text" name="customer" placeholder="Tên khách" value="<?= htmlspecialchars($_GET['customer'] ?? '') ?>">
                <button type="submit" class="nut-tim-kiem">Tìm</button>
            </div>
        </form>
    </div>

    <!-- Bảng -->
    <?php if (empty($orders)): ?>
        <p>Chưa có đơn hàng nào được giao.</p>
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
                        $statusId = $order['ma_trang_thai_don_hang'] ?? 0;
                        $statusName = $order['order_statuses']['ten_trang_thai'] ?? '—';
                    ?>
                    <tr data-order-id="<?= $order['ma_don_hang'] ?? 0 ?>">
                        <td>#<?= $order['ma_don_hang'] ?? 0 ?></td>
                        <td><?= htmlspecialchars($order['users']['ten_nguoi_dung'] ?? 'Khách lẻ') ?></td>
                        <td><?= !empty($order['ngay_dat_hang']) ? date('d/m/Y H:i', strtotime($order['ngay_dat_hang'])) : '—' ?></td>
                        <td>
                            <?php if (!empty($order['ngay_giao_hang'])): ?>
                                <span style="color:green; font-weight:600;"><?= date('d/m/Y H:i', strtotime($order['ngay_giao_hang'])) ?></span>
                            <?php else: ?>
                                <em style="color:#999">Chưa cập nhật</em>
                            <?php endif; ?>
                        </td>
                        <td><?= number_format($order['tong_gia_tri_don_hang'] ?? 0) ?>đ</td>
                        <td><span class="trang-thai status-<?= $statusId ?>"><?= $statusName ?></span></td>
                        <td>
                            <?php 
                                $maNhanVien = $order['ma_nhan_vien_xu_ly'] ?? null;
                                echo $maNhanVien ? 'ID: ' . $maNhanVien : '<em style="color:#999;">Chưa xử lý</em>';
                            ?>
                        </td>
                        <td>
                            <button class="nut-xem" data-id="<?= $order['ma_don_hang'] ?? 0 ?>">Xem</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($totalPages > 1): ?>
            <div class="phan-trang" style="margin-top:40px; margin-bottom:40px; text-align:center;">
                <?php
                $url = "index.php?c=order&a=completed";
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

<!-- POPUP -->
<div id="popup-detail" class="popup-overlay" style="display:none">
    <div class="popup-content">
        <span class="close-popup">×</span>
        <div id="popup-body"></div>
    </div>
</div>
