<?php
require_once __DIR__ . '/../model/message_model.php';
require_once __DIR__ . '/../config/supabase.php';

class MessageController {
    private $model;
    private $admin_id = 1; // admin id

    public function __construct() {
        date_default_timezone_set('Asia/Ho_Chi_Minh'); // Đảm bảo múi giờ Việt Nam
        $this->model = new MessageModel();
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?c=auth&a=login");
            exit();
        }
    }

    public function index() {
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
                    $this->model->sendMessage($chat_id, $this->admin_id, $noi_dung, $current_time);
                }
            }
        }

        $params = [
            'c' => 'message',
            'a' => 'index',
            'user_id' => $user_id,
            'filter' => $_GET['filter'] ?? 'all',
            'search' => $_GET['search'] ?? ''
        ];
        header('Location: index.php?' . http_build_query($params));
        exit();
    }
}
?>