<?php
require_once __DIR__ . '/../config/supabase.php';

class PaymentSettingsModel {

    /**
     * Lấy tất cả phương thức thanh toán
     */
    public function getAllPaymentMethods() {
        $result = supabase_request('GET', 'payment_methods_settings', [
            'select' => 'id,ma_phuong_thuc,ten_phuong_thuc,mo_ta,da_kich_hoat,thu_tu_hien_thi,icon,ngay_tao_ban_ghi,ngay_sua_ban_ghi',
            'order' => 'thu_tu_hien_thi.asc'
        ]);
        return $result['error'] ? [] : $result['data'];
    }

    /**
     * Lấy phương thức thanh toán theo mã
     */
    public function getPaymentMethodByCode($maPhuongThuc) {
        $result = supabase_request('GET', 'payment_methods_settings', [
            'select' => 'id,ma_phuong_thuc,ten_phuong_thuc,mo_ta,da_kich_hoat,thu_tu_hien_thi,icon',
            'ma_phuong_thuc' => "eq.$maPhuongThuc",
            'limit' => 1
        ]);
        return $result['error'] ? null : ($result['data'][0] ?? null);
    }

    /**
     * Cập nhật trạng thái kích hoạt của phương thức thanh toán
     */
    public function updatePaymentMethodStatus($code, $daKichHoat) {
        $update = [
            'da_kich_hoat' => $daKichHoat,
            'ngay_sua_ban_ghi' => date('Y-m-d H:i:s')
        ];
        $result = supabase_request('PATCH', 'payment_methods_settings', [
            'ma_phuong_thuc' => "eq.$code"
        ], $update);
        return !$result['error'];
    }

    /**
     * Cập nhật thông tin phương thức thanh toán
     */
    public function updatePaymentMethod($id, $data) {
        $update = [
            'ten_phuong_thuc' => $data['ten_phuong_thuc'] ?? '',
            'mo_ta' => $data['mo_ta'] ?? null,
            'thu_tu_hien_thi' => isset($data['thu_tu_hien_thi']) ? (int)$data['thu_tu_hien_thi'] : 0,
            'icon' => $data['icon'] ?? null,
            'ngay_sua_ban_ghi' => date('Y-m-d H:i:s')
        ];
        if (isset($data['da_kich_hoat'])) {
            $update['da_kich_hoat'] = (bool)$data['da_kich_hoat'];
        }
        $result = supabase_request('PATCH', 'payment_methods_settings', [
            'id' => "eq.$id"
        ], $update);
        return !$result['error'];
    }

    /**
     * Tạo phương thức thanh toán mới
     */
    public function createPaymentMethod($data) {
        $insert = [
            'ma_phuong_thuc' => $data['ma_phuong_thuc'] ?? '',
            'ten_phuong_thuc' => $data['ten_phuong_thuc'] ?? '',
            'mo_ta' => $data['mo_ta'] ?? null,
            'da_kich_hoat' => isset($data['da_kich_hoat']) ? (bool)$data['da_kich_hoat'] : true,
            'thu_tu_hien_thi' => isset($data['thu_tu_hien_thi']) ? (int)$data['thu_tu_hien_thi'] : 0,
            'icon' => $data['icon'] ?? null,
            'ngay_tao_ban_ghi' => date('Y-m-d H:i:s'),
            'ngay_sua_ban_ghi' => date('Y-m-d H:i:s')
        ];
        $result = supabase_request('POST', 'payment_methods_settings', [], $insert);
        return $result['error'] ? false : true;
    }

    /**
     * Xóa phương thức thanh toán
     */
    public function deletePaymentMethod($id) {
        $result = supabase_request('DELETE', 'payment_methods_settings', [
            'id' => "eq.$id"
        ]);
        return !$result['error'];
    }
}

?>

