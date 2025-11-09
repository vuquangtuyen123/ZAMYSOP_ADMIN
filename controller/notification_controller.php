<?php
require_once __DIR__ . '/../model/notification_model.php';
require_once __DIR__ . '/../config/auth.php';

class NotificationController {
    public function index() {
        require_login();
        
        if (!(can('message.manage_all') || can('message.reply'))) { 
            http_response_code(403); 
            echo 'Không có quyền.'; 
            exit; 
        }
        
        $adminId = 1;
        $notifications = [];
        
        try {
            $result = supabase_request('GET', 'notifications', [
                'select' => '*',
                'ma_nguoi_dung' => "eq.$adminId",
                'order' => 'thoi_gian_tao.desc',
                'limit' => 100
            ]);
            
            if (!$result['error']) {
                $notifications = $result['data'];
            }
        } catch (Exception $e) {
            error_log("Error loading notifications: " . $e->getMessage());
        }
        
        require_once __DIR__ . '/../view/notification/index.php';
    }
    
    public function markAsRead() {
        require_login();
        
        if (!(can('message.manage_all') || can('message.reply'))) { 
            http_response_code(403); 
            echo json_encode(['success' => false, 'message' => 'Không có quyền']); 
            exit; 
        }
        
        $notificationId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        
        if ($notificationId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
            exit;
        }
        
        $result = supabase_request('PATCH', 'notifications', 
            ['ma_thong_bao' => "eq.$notificationId"], 
            ['da_doc' => true]
        );
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => !$result['error'],
            'message' => $result['error'] ? 'Cập nhật thất bại' : 'Đã đánh dấu đã đọc'
        ]);
        exit;
    }
    
    public function getUnreadCount() {
        require_login();
        
        if (!(can('message.manage_all') || can('message.reply'))) { 
            http_response_code(403); 
            echo json_encode(['error' => 'Không có quyền']); 
            exit; 
        }
        
        $adminId = 1;
        $count = NotificationModel::getUnreadCount($adminId);
        
        header('Content-Type: application/json');
        echo json_encode([
            'unread_count' => $count
        ]);
        exit;
    }
    
    public function delete() {
        require_login();
        
        if (!(can('message.manage_all') || can('message.reply'))) { 
            http_response_code(403); 
            echo json_encode(['success' => false, 'message' => 'Không có quyền']); 
            exit; 
        }
        
        // Đảm bảo không có output trước JSON
        if (ob_get_level() > 0) {
            ob_clean();
        }
        header('Content-Type: application/json; charset=UTF-8');
        
        $notificationId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        
        if ($notificationId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
            exit;
        }
        
        $success = NotificationModel::deleteNotification($notificationId);
        
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Xóa thông báo thành công' : 'Không thể xóa thông báo'
        ]);
        exit;
    }
}
?>
