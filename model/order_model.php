<?php
require_once __DIR__ . '/../config/supabase.php';

class OrderModel {

    // ==================== DANH SÁCH ĐƠN HÀNG ====================
    public function searchOrders($code = '', $customer = '', $status = '', $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $params = [
            'select' => '*,users(ten_nguoi_dung,so_dien_thoai),order_statuses(ten_trang_thai)',
            'order' => 'ngay_dat_hang.desc',
            'offset' => $offset,
            'limit' => $limit
        ];

        if ($code !== '') $params['ma_don_hang'] = "eq.$code";
        if ($customer !== '') $params['users.ten_nguoi_dung'] = "ilike.*$customer*";
        if ($status !== '' && is_numeric($status)) $params['ma_trang_thai_don_hang'] = "eq.$status";

        return supabase_request('GET', 'orders', $params);
    }

    // Đếm tổng số bản ghi để hiển thị phân trang
    public function countSearchOrders($code = '', $customer = '', $status = '') {
        $params = [];
        if ($code !== '') $params['ma_don_hang'] = "eq.$code";
        if ($customer !== '') $params['users.ten_nguoi_dung'] = "ilike.*$customer*";
        if ($status !== '' && is_numeric($status)) $params['ma_trang_thai_don_hang'] = "eq.$status";

        $response = supabase_request('HEAD', 'orders', $params);
        if ($response['error'] || empty($response['status_headers']['content-range'])) {
            return 0;
        }

        // Lấy phần sau dấu "/" trong "0-9/45"
        if (preg_match('/\/(\d+)$/', $response['status_headers']['content-range'], $matches)) {
            return (int)$matches[1];
        }

        return 0;
    }

    public function searchByStatus($statusId, $code = '', $customer = '', $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $params = [
            'select' => '*,users(ten_nguoi_dung,so_dien_thoai),order_statuses(ten_trang_thai)',
            'ma_trang_thai_don_hang' => "eq.$statusId",
            'order' => 'ngay_dat_hang.desc',
            'offset' => $offset,
            'limit' => $limit
        ];

        if ($code !== '') $params['ma_don_hang'] = "eq.$code";
        if ($customer !== '') $params['users.ten_nguoi_dung'] = "ilike.*$customer*";
        return supabase_request('GET', 'orders', $params);
    }

    //  Chỉnh cho phân trang theo trạng thái
    public function countByStatusWithSearch($statusId, $code = '', $customer = '') {
        $params = ['ma_trang_thai_don_hang' => "eq.$statusId"];
        if ($code !== '') $params['ma_don_hang'] = "eq.$code";
        if ($customer !== '') $params['users.ten_nguoi_dung'] = "ilike.*$customer*";

        $response = supabase_request('HEAD', 'orders', $params);
        if ($response['error'] || empty($response['status_headers']['content-range'])) {
            return 0;
        }

        if (preg_match('/\/(\d+)$/', $response['status_headers']['content-range'], $matches)) {
            return (int)$matches[1];
        }

        return 0;
    }

    // ==================== CHI TIẾT ĐƠN HÀNG ====================
    public function getByIdFull($maDonHang) {
        $params = [
            'select' => '*,users(*),order_statuses(*)',
            'ma_don_hang' => "eq.$maDonHang"
        ];
        $response = supabase_request('GET', 'orders', $params);
        if ($response['error'] || empty($response['data']) || !isset($response['data'][0])) {
            return ['error' => true, 'data' => null];
        }
        return ['error' => false, 'data' => $response['data'][0]];
    }

    public function getOrderDetails($maDonHang) {
        $params = [
            'select' => 'ma_chi_tiet_don_hang, ma_don_hang, so_luong_mua, thanh_tien, ma_bien_the, ten_san_pham, ten_size, ten_mau, duong_dan_anh',
            'ma_don_hang' => "eq.$maDonHang",
            'order' => 'ma_chi_tiet_don_hang.asc'
        ];
        return supabase_request('GET', 'v_order_details_full', $params);
    }

    public function getStatuses() {
        $params = ['trang_thai_kich_hoat' => 'eq.true', 'order' => 'ma_trang_thai_don_hang'];
        return supabase_request('GET', 'order_statuses', $params);
    }

    // ==================== CẬP NHẬT TRẠNG THÁI + CỘNG TỒN KHO ====================
    public function updateStatus($maDonHang, $statusId, $autoDelivery = false) {
        $body = ['ma_trang_thai_don_hang' => $statusId];

        // Lưu đúng giờ Việt Nam (UTC+7)
        if ($statusId == 4 && $autoDelivery) {
            $vnTime = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
            $body['ngay_giao_hang'] = $vnTime->format('Y-m-d\TH:i:sP'); // ISO 8601 có timezone
        }

        // Kiểm tra: Nếu đơn đã ở trạng thái 5 hoặc 6 thì bỏ qua
        $currentOrder = supabase_request('GET', 'orders', [
            'select' => 'ma_trang_thai_don_hang',
            'ma_don_hang' => "eq.$maDonHang"
        ]);

        if (!$currentOrder['error'] && !empty($currentOrder['data'])) {
            $currentStatus = (int)$currentOrder['data'][0]['ma_trang_thai_don_hang'];
            if (in_array($currentStatus, [5, 6])) {
                error_log("⚠️ Đơn #$maDonHang đã ở trạng thái hủy/trả, bỏ qua cộng tồn kho.");
                return ['error' => false, 'message' => 'Bỏ qua cộng tồn kho do trạng thái trùng'];
            }
        }

        // Cập nhật trạng thái đơn hàng
        $updateOrder = supabase_request('PATCH', 'orders', ['ma_don_hang' => "eq.$maDonHang"], $body);
        if ($updateOrder['error']) {
            return ['error' => true, 'message' => 'Cập nhật trạng thái thất bại'];
        }

        // Cộng lại tồn kho nếu đơn bị hủy hoặc hoàn hàng
        if (in_array($statusId, [5, 6])) {
            $details = $this->getOrderDetails($maDonHang);
            if (!$details['error'] && !empty($details['data'])) {
                $variantTotals = [];

                foreach ($details['data'] as $item) {
                    $maBienThe = $item['ma_bien_the'] ?? null;
                    $soLuong = (int)($item['so_luong_mua'] ?? 0);
                    if (!$maBienThe || $soLuong <= 0) continue;
                    $variantTotals[$maBienThe] = ($variantTotals[$maBienThe] ?? 0) + $soLuong;
                }

                foreach ($variantTotals as $maBienThe => $tongSoLuong) {
                    $stockRes = supabase_request('GET', 'product_variants', [
                        'select' => 'ton_kho',
                        'ma_bien_the' => "eq.$maBienThe"
                    ]);

                    if ($stockRes['error'] || empty($stockRes['data'])) continue;

                    $current = (int)$stockRes['data'][0]['ton_kho'];
                    $new = $current + $tongSoLuong;

                    $patch = supabase_request(
                        'PATCH',
                        'product_variants',
                        ['ma_bien_the' => "eq.$maBienThe"],
                        ['ton_kho' => $new]
                    );

                    if ($patch['error']) {
                        error_log("Lỗi cộng tồn kho cho #$maBienThe (+$tongSoLuong)");
                    } else {
                        error_log("Đã cộng +$tongSoLuong vào tồn kho #$maBienThe ($current → $new)");
                    }
                }
            }
        }

        return ['error' => false, 'message' => 'Cập nhật thành công'];
    }
}
?>
