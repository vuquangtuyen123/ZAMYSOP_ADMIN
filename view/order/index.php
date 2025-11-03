
<head>
    <meta charset="UTF-8">
    <title>Quản lý Đơn hàng</title>
</head>
<div class="noi-dung-chinh">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <h2 style="margin:0;">Tất cả đơn hàng</h2>
        <button onclick="location.reload()" class="nut-tai-lai">Tải lại</button>
    </div>

    <!-- Tìm kiếm -->
    <div class="tim-kiem-don-hang">
        <form method="GET" action="index.php">
            <input type="hidden" name="c" value="order">
            <input type="hidden" name="a" value="index">
            <div class="nhom-input">
                <input type="text" name="code" placeholder="Mã đơn" value="<?= htmlspecialchars($_GET['code'] ?? '') ?>">
                <input type="text" name="customer" placeholder="Tên khách" value="<?= htmlspecialchars($_GET['customer'] ?? '') ?>">
                <select name="status">
                    <option value="">Tất cả trạng thái</option>
                    <?php foreach ($statuses as $s): ?>
                        <option value="<?= $s['ma_trang_thai_don_hang'] ?>" <?= ($_GET['status'] ?? '') == $s['ma_trang_thai_don_hang'] ? 'selected' : '' ?>>
                            <?= $s['ten_trang_thai'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="nut-tim-kiem">Tìm</button>
            </div>
        </form>
    </div>

    <!-- Bảng -->
    <?php if (empty($orders)): ?>
        <p>Không có đơn hàng nào.</p>
    <?php else: ?>
        <div class="bang-du-lieu">
            <table>
                <thead>
                    <tr>
                        <th>Mã ĐH</th>
                        <th>Khách hàng</th>
                        <th>Ngày đặt</th>
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
                    ?>
                    <tr data-order-id="<?= $order['ma_don_hang'] ?>">
                        <td>#<?= $order['ma_don_hang'] ?></td>
                        <td><?= htmlspecialchars($order['users']['ten_nguoi_dung'] ?? 'Khách lẻ') ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($order['ngay_dat_hang'])) ?></td>
                        <td><?= number_format($order['tong_gia_tri_don_hang']) ?>đ</td>
                        <td><span class="trang-thai status-<?= $statusId ?>"><?= $statusName ?></span></td>
                        <td>
                            <button class="nut-xem" data-id="<?= $order['ma_don_hang'] ?>">Xem</button>
                          <?php if ($statusId == 1): ?>
    <button class="nut-xac-nhan" data-id="<?= $order['ma_don_hang'] ?>" data-action="confirm">Xác nhận</button>
    <button class="nut-huy-don" data-id="<?= $order['ma_don_hang'] ?>" data-action="cancel">Hủy đơn</button>
<?php elseif ($statusId == 2): ?>
    <button class="nut-giao-hang" data-id="<?= $order['ma_don_hang'] ?>" data-action="deliver">Giao hàng</button>
<?php elseif ($statusId == 3): ?>
    <button class="nut-da-giao" data-id="<?= $order['ma_don_hang'] ?>" data-action="complete">Đã giao</button>
<?php elseif ($canReturn && in_array($statusId, [1,2,3,4])): ?>
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
                $url = "index.php?c=order&a=index";
                $url .= $code ? "&code=" . urlencode($code) : '';
                $url .= $customer ? "&customer=" . urlencode($customer) : '';
                $url .= $status ? "&status=$status" : '';
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