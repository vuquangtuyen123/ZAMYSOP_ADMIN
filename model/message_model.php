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
        $params = [
            'select' => 'ma_chat, ma_nguoi_dung_1, ma_nguoi_dung_2, ngay_cap_nhat, trang_thai',
            'order' => 'ngay_cap_nhat.desc'
        ];
        $res = supabase_request('GET', 'chats', $params);
        if ($res['error'] || empty($res['data'])) {
            error_log("getChatsByAdminWithSearch - supabase returned empty/error: " . print_r($res, true));
            return [];
        }

        $all_chats = $res['data'];
        $by_user = []; // key = user_id

        foreach ($all_chats as $chat) {
            if ($chat['ma_nguoi_dung_1'] != $admin_id && $chat['ma_nguoi_dung_2'] != $admin_id) {
                continue;
            }

            $user_id = ($chat['ma_nguoi_dung_1'] == $admin_id) ? $chat['ma_nguoi_dung_2'] : $chat['ma_nguoi_dung_1'];
            if (!$user_id) continue;

            $user_info = $this->getUserById($user_id);
            if (!$user_info) continue;

            if (!empty($search)) {
                $search_lower = mb_strtolower(trim($search));
                $name_lower = mb_strtolower($user_info['ten_nguoi_dung'] ?? '');
                if (strpos($name_lower, $search_lower) === false) {
                    continue;
                }
            }

            $unread_count = $this->getUnreadCountForChat($chat['ma_chat'], $admin_id);
            $last_message = $this->getLastMessage($chat['ma_chat']);

            if ($filter === 'unread' && $unread_count == 0) continue;
            if ($filter === 'read' && $unread_count > 0) continue;

            if (!isset($by_user[$user_id]) || strtotime($chat['ngay_cap_nhat']) > strtotime($by_user[$user_id]['ngay_cap_nhat'])) {
                $chat['user_id'] = $user_id;
                $chat['user_name'] = $user_info['ten_nguoi_dung'] ?? 'Người dùng';
                $chat['avatar'] = $user_info['avatar'] ?? '';
                $chat['last_message'] = $last_message;
                $chat['unread_count'] = $unread_count;
                $by_user[$user_id] = $chat;
            }
        }

        $list = array_values($by_user);
        usort($list, fn($a, $b) => strtotime($b['ngay_cap_nhat']) - strtotime($a['ngay_cap_nhat']));
        return $list;
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