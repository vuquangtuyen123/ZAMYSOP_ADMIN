<?php
require_once __DIR__ . '/../model/message_model.php';
require_once __DIR__ . '/../config/supabase.php';

class MessageController {
    private $model;
    private $admin_id = 1; // admin id

    public function __construct() {
        $this->model = new MessageModel();
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?c=auth&a=login");
            exit();
        }
    }

    public function index() {
        $filter = $_GET['filter'] ?? 'all';
        $search = trim($_GET['search'] ?? '');
        $user_id = $_GET['user_id'] ?? null; // note: now we use user_id to select chat thread (aggregated)
        $messages = [];
        $user_name = '';
        $noResults = false;

        // lấy list users (mỗi user 1 entry)
        $chats = $this->model->getChatsByAdminWithSearch($this->admin_id, $search);

        // tổng unread/read (tính từ $chats)
        $total_unread = array_reduce($chats, fn($carry,$c) => $carry + (isset($c['unread_count']) ? (int)$c['unread_count'] : 0), 0);
        $total_all = count($chats);
        $total_read = $total_all; // we can compute differently if needed

        if (!empty($search) && empty($chats)) {
            $noResults = true;
        } elseif ($user_id) {
            // kiểm tra user có trong danh sách
            $found = null;
            foreach ($chats as $c) {
                if ($c['user_id'] == $user_id) { $found = $c; break; }
            }
            if ($found) {
                // lấy tất cả messages giữa user và admin
                $messages = $this->model->getMessagesByUser($user_id, $this->admin_id);
                // đánh dấu read cho tất cả messages của user
                $this->model->markUserMessagesAsRead($user_id, $this->admin_id);
                $user_name = $found['user_name'] ?? '';
            } else {
                $noResults = true;
            }
        }

        require_once __DIR__ . '/../view/message/index.php';
    }

    public function send() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['noi_dung'])) {
            // We expect a chat_id to exist for sending; if user_id posted, choose latest chat for that user
            $chat_id = $_POST['chat_id'] ?? null;
            $user_id = $_POST['user_id'] ?? null;
            $noi_dung = trim($_POST['noi_dung'] ?? '');

            if (!$chat_id && $user_id) {
                // tìm chat id mới nhất giữa user và admin
                $chats = supabase_request('GET', 'chats', [
                    'or' => "(and(ma_nguoi_dung_1.eq.$user_id,ma_nguoi_dung_2.eq.{$this->admin_id}),and(ma_nguoi_dung_1.eq.{$this->admin_id},ma_nguoi_dung_2.eq.$user_id))",
                    'order' => 'ngay_cap_nhat.desc',
                    'limit' => 1
                ]);
                if (!empty($chats['data'])) $chat_id = $chats['data'][0]['ma_chat'];
            }

            if ($chat_id && $noi_dung) {
                date_default_timezone_set('UTC');
                $current_time = gmdate('Y-m-d H:i:s');
                $this->model->sendMessage($chat_id, $this->admin_id, $noi_dung, $current_time);
            }

            // keep state
            $_GET['user_id'] = $user_id;
            $_GET['filter'] = $_GET['filter'] ?? 'all';
            $_GET['search'] = $_GET['search'] ?? '';
        }

        $this->index();
    }

    public function delete() {
        $chat_id = $_GET['chat_id'] ?? null;
        if ($chat_id) {
            $this->model->deleteChat($chat_id);
            unset($_GET['user_id']);
        }
        $this->index();
    }
}
