<?php
require_once __DIR__ . '/../config/supabase.php';

class NotificationModel {
    /**
     * Tạo thông báo cho admin (ID = 1)
     */
    public static function createAdminNotification($title, $content, $type = 'system', $orderId = null, $discountId = null) {
        $adminId = 1; // Admin ID
        
        $body = [
            'ma_nguoi_dung' => $adminId,
            'tieu_de' => $title,
            'noi_dung' => $content,
            'loai_thong_bao' => $type,
            'da_doc' => false,
        ];
        
        if ($orderId !== null) {
            $body['ma_don_hang'] = $orderId;
        }
        
        if ($discountId !== null) {
            $body['ma_khuyen_mai'] = $discountId;
        }
        
        $result = supabase_request('POST', 'notifications', [], $body);
        
        return !$result['error'];
    }
    
    /**
     * Tạo thông báo khi có tin nhắn mới từ user
     */
    public static function notifyNewMessage($userId, $userName, $messageContent) {
        $title = "Tin nhắn mới từ $userName";
        $content = "Bạn có tin nhắn mới từ $userName: " . mb_substr($messageContent, 0, 100) . (mb_strlen($messageContent) > 100 ? '...' : '');
        
        return self::createAdminNotification($title, $content, 'system');
    }
    
    /**
     * Tạo thông báo khi có yêu cầu hoàn hàng
     */
    public static function notifyReturnRequest($orderId, $orderCode, $customerName, $note = '') {
        $title = "Yêu cầu hoàn hàng - Đơn #$orderCode";
        $content = "Khách hàng $customerName đã yêu cầu hoàn hàng cho đơn hàng #$orderCode";
        if (!empty($note)) {
            $content .= "\nGhi chú: " . $note;
        }
        
        return self::createAdminNotification($title, $content, 'order', $orderId);
    }
    
    /**
     * Lấy số lượng thông báo chưa đọc của admin
     */
    public static function getUnreadCount($adminId = 1) {
        // Dùng select => 'count' để tối ưu
        $params = [
            'select' => 'count',
            'ma_nguoi_dung' => "eq.$adminId",
            'da_doc' => 'eq.false'
        ];
        
        $result = supabase_request('GET', 'notifications', $params);
        
        if ($result['error']) {
            return 0;
        }
        
        // Supabase có thể trả về count dưới dạng [{count: number}] hoặc số lượng items
        if (isset($result['data'][0]['count'])) {
            // Format: [{count: number}]
            return (int)$result['data'][0]['count'];
        } elseif (isset($result['data']) && is_array($result['data'])) {
            // Format: array của objects (nếu select không phải 'count')
            return count($result['data']);
        }
        
        // Fallback: Lấy tất cả và đếm thủ công
        $params2 = [
            'select' => 'ma_thong_bao',
            'ma_nguoi_dung' => "eq.$adminId",
            'da_doc' => 'eq.false'
        ];
        $result2 = supabase_request('GET', 'notifications', $params2);
        if ($result2['error']) {
            return 0;
        }
        return count($result2['data'] ?? []);
    }
    
    /**
     * Xóa thông báo
     */
    public static function deleteNotification($notificationId) {
        $result = supabase_request('DELETE', 'notifications', [
            'ma_thong_bao' => "eq.$notificationId"
        ]);
        
        return !$result['error'];
    }
}
?>

