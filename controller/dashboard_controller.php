<?php
require_once __DIR__ . '/../model/dashboard_model.php';

class DashboardController {

    /** Trang Dashboard chính */
    public function index() {
      $filterType = $_GET['type'] ?? null;
    $filterValue = $_GET['value'] ?? null;

    $summary = DashboardModel::layTongQuan($filterType, $filterValue);
    $categoryRevenue = DashboardModel::layDoanhThuTheoDanhMuc($filterType, $filterValue);
    $topProducts = DashboardModel::layTop5SanPham($filterType, $filterValue);
    $cancelStats = DashboardModel::layTop5TyLeHuy($filterType, $filterValue);
    $returnStats = DashboardModel::layTop5TyLeHoan($filterType, $filterValue);
    $canhBao = DashboardModel::layCanhBaoHomNay();

    // THÊM MỚI: Tất cả sản phẩm đã bán
    $salesByProduct = DashboardModel::laySoLuongBanRaTheoSanPham($filterType, $filterValue);

    include __DIR__ . '/../view/dashboard.php';
    }

    /** API cho biểu đồ doanh thu (trả JSON sạch) */
    public function apiRevenue() {
        header('Content-Type: application/json; charset=utf-8');
        $type = $_GET['type'] ?? 'month';
        $value = $_GET['value'] ?? null;

        $data = DashboardModel::layDoanhThuTheoThoiGian($type, $value);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** API lấy số lượng notification và message chưa đọc (cho realtime update) */
    public function getCounts() {
        require_login();
        header('Content-Type: application/json; charset=utf-8');
        
        require_once __DIR__ . '/../model/notification_model.php';
        require_once __DIR__ . '/../model/message_model.php';
        
        $adminId = 1;
        $notificationCount = NotificationModel::getUnreadCount($adminId);
        
        $messageCount = 0;
        try {
            $messageModel = new MessageModel();
            $chats = $messageModel->getChatsByAdminWithSearch($adminId, '', 'unread');
            // Đếm số lượng người chưa đọc (mỗi chat = 1 người), không phải tổng số tin nhắn
            foreach ($chats as $chat) {
                if ((int)($chat['unread_count'] ?? 0) > 0) {
                    $messageCount++;
                }
            }
        } catch (Exception $e) {
            error_log("Error getting unread message count: " . $e->getMessage());
        }
        
        echo json_encode([
            'success' => true,
            'notification_count' => $notificationCount,
            'message_count' => $messageCount
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?>
