<?php
require_once __DIR__ . '/../model/message_model.php';
require_once __DIR__ . '/../config/supabase.php';
require_once __DIR__ . '/../config/auth.php';

class MessageController {
    private $model;
    private $admin_id = 1; // admin id

    public function __construct() {
        date_default_timezone_set('Asia/Ho_Chi_Minh'); // Đảm bảo múi giờ Việt Nam
        $this->model = new MessageModel();
        require_login();
    }

    public function index() {
        if (!(can('message.manage_all') || can('message.reply'))) { http_response_code(403); echo 'Không có quyền.'; exit; }
        $filter = $_GET['filter'] ?? 'all';
        $search = trim($_GET['search'] ?? '');
        $user_id = $_GET['user_id'] ?? null;
        
        $chats = $this->model->getChatsByAdminWithSearch($this->admin_id, $search, $filter);
        $all_chats = $this->model->getChatsByAdminWithSearch($this->admin_id, '', 'all');
        
        $total_all = count($all_chats);
        $total_unread = count(array_filter($all_chats, fn($c) => (int)($c['unread_count'] ?? 0) > 0));
        $total_read = count(array_filter($all_chats, fn($c) => (int)($c['unread_count'] ?? 0) === 0));

        $total_unread_display = $total_unread;
        $total_read_display = $total_read;
        
        if ($filter === 'unread') {
            $total_read_display = $total_all - count($chats);
        } elseif ($filter === 'read') {
            $total_unread_display = $total_all - count($chats);
        }

        $messages = [];
        $user_name = '';
        $noResults = false;

        if (!empty($search) && empty($chats) && empty($user_id)) {
            $noResults = true;
        }

        if ($user_id) {
            $found_chat = null;
            foreach ($all_chats as $chat) {
                if ($chat['user_id'] == $user_id) {
                    $found_chat = $chat;
                    break;
                }
            }
            
            if ($found_chat) {
                $messages = $this->model->getMessagesByUser($user_id, $this->admin_id);
                $this->model->markUserMessagesAsRead($user_id, $this->admin_id);
                $user_name = $found_chat['user_name'] ?? 'Người dùng';
            } else {
                $noResults = true;
            }
        }

        require_once __DIR__ . '/../view/message/index.php';
    }

    public function send() {
        if (!(can('message.manage_all') || can('message.reply'))) { http_response_code(403); echo 'Không có quyền.'; exit; }
        
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['noi_dung'])) {
            $user_id = $_POST['user_id'] ?? $_GET['user_id'] ?? null;
            $noi_dung = trim($_POST['noi_dung']);

            if ($user_id && $noi_dung) {
                $chats = supabase_request('GET', 'chats', [
                    'or' => "(and(ma_nguoi_dung_1.eq.$user_id,ma_nguoi_dung_2.eq.{$this->admin_id}),and(ma_nguoi_dung_1.eq.{$this->admin_id},ma_nguoi_dung_2.eq.$user_id))",
                    'order' => 'ngay_cap_nhat.desc',
                    'limit' => 1
                ]);
                
                if (!empty($chats['data'])) {
                    $chat_id = $chats['data'][0]['ma_chat'];
                    // Tạo thời gian ở múi giờ Việt Nam và chuyển sang UTC
                    $date = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
                    $date->setTimezone(new DateTimeZone('UTC'));
                    $current_time = $date->format('Y-m-d H:i:s');
                    $result = $this->model->sendMessage($chat_id, $this->admin_id, $noi_dung, $current_time);
                    
                    if ($isAjax) {
                        // Trả về JSON cho AJAX request
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => !$result['error'],
                            'message' => $result['error'] ? 'Lỗi khi gửi tin nhắn' : 'Gửi tin nhắn thành công'
                        ]);
                        exit;
                    }
                }
            }
        }

        // Redirect cho form submit thông thường
        $params = [
            'c' => 'message',
            'a' => 'index',
            'user_id' => $user_id ?? null,
            'filter' => $_GET['filter'] ?? 'all',
            'search' => $_GET['search'] ?? ''
        ];
        header('Location: index.php?' . http_build_query($params));
        exit();
    }

    /**
     * API endpoint để lấy tin nhắn mới (cho realtime)
     * Trả về JSON: { messages: [...], last_message_id: ... }
     */
    public function getNewMessages() {
        if (!(can('message.manage_all') || can('message.reply'))) { 
            http_response_code(403); 
            echo json_encode(['error' => 'Không có quyền']); 
            exit; 
        }
        
        $user_id = (int)($_GET['user_id'] ?? 0);
        $last_message_id = (int)($_GET['last_message_id'] ?? 0);
        
        if (!$user_id) {
            echo json_encode(['error' => 'Thiếu user_id']);
            exit;
        }
        
        $messages = $this->model->getMessagesByUser($user_id, $this->admin_id);
        
        // Lọc chỉ lấy tin nhắn mới hơn last_message_id
        $new_messages = [];
        foreach ($messages as $msg) {
            if ((int)$msg['ma_tin_nhan'] > $last_message_id) {
                $new_messages[] = $msg;
            }
        }
        
        // Đánh dấu đã đọc nếu có tin nhắn mới từ user
        if (!empty($new_messages)) {
            $this->model->markUserMessagesAsRead($user_id, $this->admin_id);
        }
        
        $max_id = $last_message_id;
        if (!empty($messages)) {
            $max_id = max(array_column($messages, 'ma_tin_nhan'));
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'messages' => $new_messages,
            'last_message_id' => $max_id,
            'count' => count($new_messages)
        ]);
        exit;
    }

    /**
     * API endpoint để cập nhật danh sách chat (cho realtime)
     * Trả về JSON: { chats: [...], unread_counts: {...} }
     */
    public function getChatsUpdate() {
        if (!(can('message.manage_all') || can('message.reply'))) { 
            http_response_code(403); 
            echo json_encode(['error' => 'Không có quyền']); 
            exit; 
        }
        
        $search = trim($_GET['search'] ?? '');
        $filter = $_GET['filter'] ?? 'all';
        
        $chats = $this->model->getChatsByAdminWithSearch($this->admin_id, $search, $filter);
        $all_chats = $this->model->getChatsByAdminWithSearch($this->admin_id, '', 'all');
        
        $total_all = count($all_chats);
        $total_unread = count(array_filter($all_chats, fn($c) => (int)($c['unread_count'] ?? 0) > 0));
        $total_read = count(array_filter($all_chats, fn($c) => (int)($c['unread_count'] ?? 0) === 0));
        
        header('Content-Type: application/json');
        echo json_encode([
            'chats' => $chats,
            'total_all' => $total_all,
            'total_unread' => $total_unread,
            'total_read' => $total_read
        ]);
        exit;
    }
}
?>