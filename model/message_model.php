<?php
/**
 * MessageModel - FINAL
 * - Lấy danh sách users đã chat với admin (gộp nhiều chat nếu có)
 * - Truy vấn users bằng cột `id`
 * - Lấy toàn bộ messages của một user (gộp tất cả ma_chat liên quan)
 */
class MessageModel {
    /**
     * Trả về danh sách chats theo user (mỗi user 1 entry), newest-first.
     * Mỗi item chứa ma_chat (the latest chat id between user & admin), user_id, user_name, avatar, last_message, unread_count, ngay_cap_nhat
     */
    public function getChatsByAdminWithSearch($admin_id, $search = '', $filter = 'all') {
        // 1) Lấy tất cả chat có liên quan tới admin, sort mới → cũ (giới hạn để đỡ tải nặng)
        $params = [
            'select' => 'ma_chat,ma_nguoi_dung_1,ma_nguoi_dung_2,ngay_cap_nhat,trang_thai',
            'or' => "(ma_nguoi_dung_1.eq.$admin_id,ma_nguoi_dung_2.eq.$admin_id)",
            'order' => 'ngay_cap_nhat.desc',
            'limit' => 500
        ];
        $res = supabase_request('GET', 'chats', $params);
        if ($res['error'] || empty($res['data'])) {
            error_log("getChatsByAdminWithSearch - supabase returned empty/error: " . print_r($res, true));
            return [];
        }

        $all_chats = $res['data'];

        // Tập hợp user và chat id liên quan
        $userIds = [];
        $chatIds = [];
        foreach ($all_chats as $chat) {
            $chatIds[] = $chat['ma_chat'];
            $uid = ($chat['ma_nguoi_dung_1'] == $admin_id) ? $chat['ma_nguoi_dung_2'] : $chat['ma_nguoi_dung_1'];
            if ($uid) $userIds[$uid] = true;
        }
        $chatIds = array_values(array_unique($chatIds));
        $userIds = array_map('intval', array_keys($userIds));

        // 2) Lấy thông tin users theo batch
        $usersMap = [];
        if (!empty($userIds)) {
            $inUsers = '(' . implode(',', $userIds) . ')';
            $uRes = supabase_request('GET', 'users', [
                'select' => 'id,ten_nguoi_dung,avatar',
                'id' => "in.$inUsers"
            ]);
            if (!$uRes['error']) {
                foreach ($uRes['data'] as $u) {
                    $usersMap[$u['id']] = $u;
                }
            }
        }

        // 3) Lấy tin nhắn mới nhất cho mỗi chat (một lần query, order theo ma_chat asc, thoi_gian_gui desc)
        $lastMsgPerChat = [];
        if (!empty($chatIds)) {
            $inChats = '(' . implode(',', $chatIds) . ')';
            $mRes = supabase_request('GET', 'chat_messages', [
                'select' => 'ma_chat,ma_tin_nhan,noi_dung,thoi_gian_gui,ma_nguoi_gui',
                'ma_chat' => "in.$inChats",
                'order' => 'ma_chat.asc,thoi_gian_gui.desc'
            ]);
            if (!$mRes['error']) {
                foreach ($mRes['data'] as $m) {
                    $cid = $m['ma_chat'];
                    if (!isset($lastMsgPerChat[$cid])) {
                        $lastMsgPerChat[$cid] = $m; // bản ghi đầu tiên theo thứ tự là mới nhất cho chat đó
                    }
                }
            }
        }

        // 4) Lấy tất cả tin nhắn chưa đọc theo batch và đếm theo chat
        $unreadCountPerChat = [];
        if (!empty($chatIds)) {
            $inChats = '(' . implode(',', $chatIds) . ')';
            $urRes = supabase_request('GET', 'chat_messages', [
                'select' => 'ma_chat,ma_tin_nhan',
                'ma_chat' => "in.$inChats",
                'ma_nguoi_gui' => "neq.$admin_id",
                'da_doc' => 'eq.false'
            ]);
            if (!$urRes['error']) {
                foreach ($urRes['data'] as $r) {
                    $cid = $r['ma_chat'];
                    $unreadCountPerChat[$cid] = ($unreadCountPerChat[$cid] ?? 0) + 1;
                }
            }
        }

        // 5) Gộp theo user: chọn chat có ngay_cap_nhat lớn nhất, tổng số unread của mọi chat của user
        $by_user = []; // key = user_id

        foreach ($all_chats as $chat) {
            $user_id = ($chat['ma_nguoi_dung_1'] == $admin_id) ? $chat['ma_nguoi_dung_2'] : $chat['ma_nguoi_dung_1'];
            if (!$user_id) continue;
            $user_info = $usersMap[$user_id] ?? null;
            if (!$user_info) continue;

            // Tính tổng unread cho user: cộng tất cả chat của user
            $userUnread = ($by_user[$user_id]['unread_count'] ?? 0) + ($unreadCountPerChat[$chat['ma_chat']] ?? 0);

            // Lấy last message của chat hiện tại
            $last_message = $lastMsgPerChat[$chat['ma_chat']] ?? null;

            // Chọn chat có ngay_cap_nhat mới nhất làm đại diện
            $shouldReplace = !isset($by_user[$user_id]) || strtotime($chat['ngay_cap_nhat']) > strtotime($by_user[$user_id]['ngay_cap_nhat']);
            if ($shouldReplace) {
                $item = $chat; // includes ngay_cap_nhat
                $item['user_id'] = $user_id;
                $item['user_name'] = $user_info['ten_nguoi_dung'] ?? 'Người dùng';
                $item['avatar'] = $user_info['avatar'] ?? '';
                $item['last_message'] = $last_message;
                $item['unread_count'] = $userUnread; // set current total
                $by_user[$user_id] = $item;
            } else {
                // Không thay thế, chỉ cập nhật tổng unread
                $by_user[$user_id]['unread_count'] = $userUnread;
            }
        }

        // 6) Áp dụng tìm kiếm theo tên và filter unread/read
        $out = [];
        $search_lower = mb_strtolower(trim($search));
        foreach ($by_user as $user_id => $row) {
            if ($search !== '') {
                $name_lower = mb_strtolower($row['user_name'] ?? '');
                if (strpos($name_lower, $search_lower) === false) continue;
            }
            if ($filter === 'unread' && (int)($row['unread_count'] ?? 0) === 0) continue;
            if ($filter === 'read' && (int)($row['unread_count'] ?? 0) > 0) continue;
            $out[] = $row;
        }

        usort($out, fn($a, $b) => strtotime($b['ngay_cap_nhat']) - strtotime($a['ngay_cap_nhat']));
        return $out;
    }

    /**
     * Lấy thông tin user từ bảng users (cột id)
     */
    private function getUserById($user_id) {
        $params = [
            'select' => 'ten_nguoi_dung, avatar',
            'id' => "eq.$user_id",
            'limit' => 1
        ];
        $res = supabase_request('GET', 'users', $params);
        if ($res['error'] || empty($res['data'])) {
            error_log("getUserById - not found id=$user_id; supabase res: " . print_r($res, true));
            return null;
        }
        return $res['data'][0];
    }

    /**
     * Lấy tất cả messages của một user (gộp mọi ma_chat có user và admin)
     * Trả về mảng messages theo thoi_gian_gui.asc
     */
    public function getMessagesByUser($user_id, $admin_id) {
        $params = [
            'select' => 'ma_chat',
            'or' => "(and(ma_nguoi_dung_1.eq.$user_id,ma_nguoi_dung_2.eq.$admin_id),and(ma_nguoi_dung_1.eq.$admin_id,ma_nguoi_dung_2.eq.$user_id))"
        ];
        $res = supabase_request('GET', 'chats', $params);
        if ($res['error'] || empty($res['data'])) {
            return [];
        }

        $chat_ids = array_column($res['data'], 'ma_chat');
        if (empty($chat_ids)) return [];

        $in_list = implode(',', $chat_ids);

        $msg_params = [
            'select' => '*, users:ma_nguoi_gui(ten_nguoi_dung, avatar)',
            'ma_chat' => "in.($in_list)",
            'order' => 'thoi_gian_gui.asc'
        ];
        $mres = supabase_request('GET', 'chat_messages', $msg_params);
        if ($mres['error'] || empty($mres['data'])) return [];

        $msgs = [];
        foreach ($mres['data'] as $m) {
            $msgs[$m['ma_tin_nhan']] = $m;
        }
        return array_values($msgs);
    }

    /**
     * Lấy tin nhắn cuối cùng của 1 chat (giữ để hiển thị preview)
     */
    public function getLastMessage($chat_id) {
        $params = [
            'select' => '*',
            'ma_chat' => "eq.$chat_id",
            'order' => 'thoi_gian_gui.desc',
            'limit' => 1
        ];
        $res = supabase_request('GET', 'chat_messages', $params);
        if ($res['error'] || empty($res['data'])) return null;
        return $res['data'][0];
    }

    /**
     * Đếm unread cho 1 chat (dùng cho preview)
     */
    public function getUnreadCountForChat($chat_id, $admin_id) {
        $params = [
            'select' => 'count',
            'ma_chat' => "eq.$chat_id",
            'ma_nguoi_gui' => "neq.$admin_id",
            'da_doc' => 'eq.false'
        ];
        $res = supabase_request('GET', 'chat_messages', $params);
        return ($res['error'] || empty($res['data'])) ? 0 : (int)$res['data'][0]['count'];
    }

    /**
     * Đánh dấu tất cả messages của user (trong mọi chat với admin) là đã đọc
     */
    public function markUserMessagesAsRead($user_id, $admin_id) {
        $params = [
            'select' => 'ma_chat',
            'or' => "(and(ma_nguoi_dung_1.eq.$user_id,ma_nguoi_dung_2.eq.$admin_id),and(ma_nguoi_dung_1.eq.$admin_id,ma_nguoi_dung_2.eq.$user_id))"
        ];
        $res = supabase_request('GET', 'chats', $params);
        if ($res['error'] || empty($res['data'])) return;

        foreach ($res['data'] as $c) {
            supabase_request('PATCH', 'chat_messages', [
                'ma_chat' => "eq." . $c['ma_chat'],
                'ma_nguoi_gui' => "neq.$admin_id",
                'da_doc' => 'eq.false'
            ], ['da_doc' => true]);
        }
    }

    /**
     * Gửi message vào 1 chat id (giữ nguyên chức năng)
     */
    public function sendMessage($chat_id, $sender_id, $noi_dung, $thoi_gian_gui) {
        $body = [
            'ma_chat' => $chat_id,
            'ma_nguoi_gui' => $sender_id,
            'noi_dung' => $noi_dung,
            'loai_tin_nhan' => 'text',
            'da_doc' => false,
            'thoi_gian_gui' => $thoi_gian_gui
        ];
        $res = supabase_request('POST', 'chat_messages', [], $body);
        if (!$res['error']) {
            supabase_request('PATCH', 'chats', ['ma_chat' => "eq.$chat_id"], ['ngay_cap_nhat' => $thoi_gian_gui]);
            
            // Nếu người gửi không phải admin (ID = 1), tạo thông báo cho admin
            if ($sender_id != 1) {
                // Lấy thông tin user
                $userRes = supabase_request('GET', 'users', [
                    'select' => 'id,ten_nguoi_dung',
                    'id' => "eq.$sender_id",
                    'limit' => 1
                ]);
                
                if (!$userRes['error'] && !empty($userRes['data'])) {
                    $userName = $userRes['data'][0]['ten_nguoi_dung'] ?? 'Người dùng';
                    require_once __DIR__ . '/notification_model.php';
                    NotificationModel::notifyNewMessage($sender_id, $userName, $noi_dung);
                }
            }
        }
        return $res;
    }

    /**
     * Xóa chat theo ma_chat
     */
    public function deleteChat($chat_id) {
        supabase_request('DELETE', 'chat_messages', ['ma_chat' => "eq.$chat_id"]);
        supabase_request('DELETE', 'chats', ['ma_chat' => "eq.$chat_id"]);
    }
}
?>