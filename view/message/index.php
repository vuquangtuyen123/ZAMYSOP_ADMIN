<?php
error_reporting(E_ALL & ~E_NOTICE);
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Tính lastMessageId cho JavaScript
$last_message_id_js = 0;
if (!empty($messages)) {
    $message_ids = array_column($messages, 'ma_tin_nhan');
    if (!empty($message_ids)) {
        $last_message_id_js = max($message_ids);
    }
}
?>

<?php include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="assets/css/message.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/danhmuc.js"></script>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Tin nhắn</title>
</head>
<body>
<div class="khung-messenger">

    <!--Cột bên trái: Danh sách user đã chat -->
    <div class="ben-trai">
        <h2>Danh sách Chat</h2>
        <div class="hop-tim-kiem">
            <form method="GET" action="index.php" id="searchForm">
                <input type="hidden" name="c" value="message">
                <input type="hidden" name="a" value="index">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($filter ?? 'all') ?>">
                <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Tìm kiếm người dùng..." id="searchInput" onkeypress="if(event.key === 'Enter') submitSearch(event)">
                <button type="submit" onclick="submitSearch(event)"><i class=""></i></button>
            </form>
            <button onclick="reloadPage()" class="nut-tai-lai">Tải lại</button>
        </div>

        <div class="loc">
            <a href="index.php?c=message&a=index&filter=all&search=<?= urlencode($search ?? '') ?>">Tất cả (<?= $total_all ?>)</a> |
            <a href="index.php?c=message&a=index&filter=unread&search=<?= urlencode($search ?? '') ?>">Chưa đọc (<?= isset($total_unread_display) ? $total_unread_display : $total_unread ?>)</a> |
            <a href="index.php?c=message&a=index&filter=read&search=<?= urlencode($search ?? '') ?>">Đã đọc (<?= isset($total_read_display) ? $total_read_display : $total_read ?>)</a>
        </div>

        <div class="danh-sach-chat-container">
            <?php if ($noResults): ?>
                <p class="khong-co-ket-qua">Không có kết quả phù hợp</p>
            <?php elseif (empty($chats)): ?>
                <p class="khong-co-ket-qua">Không có chat nào</p>
            <?php else: ?>
                <?php foreach ($chats as $chat): ?>
                    <div class="item-chat <?= ($chat['unread_count'] ?? 0) > 0 ? 'chua-doc' : '' ?>"
                         onclick="location.href='index.php?c=message&a=index&user_id=<?= $chat['user_id'] ?>&filter=<?= $filter ?? 'all' ?>&search=<?= urlencode($search ?? '') ?>'">
                        <?php if (!empty($chat['avatar'])): ?>
                            <img src="<?= htmlspecialchars($chat['avatar']) ?>" alt="Avatar" class="hinh-avatar">
                        <?php else: ?>
                            <div class="icon-avatar"><i class="fas fa-user"></i></div>
                        <?php endif; ?>

                        <div class="thong-tin-chat">
                            <strong><?= htmlspecialchars($chat['user_name']) ?></strong><br>
                            <?php if (!empty($chat['last_message'])): ?>
                                <small><?= htmlspecialchars($chat['last_message']['noi_dung']) ?></small><br>
                            <?php endif; ?>
                            <small>Cập nhật: <?= date('d.m.Y H:i', strtotime($chat['ngay_cap_nhat'])) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!--  Cột bên phải: Chi tiết tin nhắn -->
    <div class="ben-phai">
        <?php if ($noResults): ?>
            <p class="khong-co-ket-qua">Không có kết quả phù hợp</p>
        <?php elseif (!empty($user_name)): ?>
            <h2>Chat với <?= htmlspecialchars($user_name) ?></h2>

            <div class="khung-tin-nhan">
                <?php if (!empty($messages)): ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="tin-nhan <?= ($msg['ma_nguoi_gui'] == 1) ? 'admin' : 'nguoi-dung' ?>">
                            <?php if (!empty($msg['users']['avatar'])): ?>
                                <img src="<?= htmlspecialchars($msg['users']['avatar']) ?>" alt="Avatar" class="hinh-avatar">
                            <?php else: ?>
                                <div class="icon-avatar"><i class="fas fa-user"></i></div>
                            <?php endif; ?>

                            <div class="noi-dung-tin-nhan">
                                <strong><?= htmlspecialchars($msg['users']['ten_nguoi_dung'] ?? ($msg['ma_nguoi_gui']==1 ? 'Admin' : 'User')) ?>:</strong>
                                <?= htmlspecialchars($msg['noi_dung']) ?><br>
                                <small><?= date('d.m.Y H:i', strtotime($msg['thoi_gian_gui'])) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Chưa có tin nhắn nào.</p>
                <?php endif; ?>
            </div>

            <div class="form-gui-tin">
                <form method="POST" action="index.php?c=message&a=send&filter=<?= $filter ?? 'all' ?>&search=<?= urlencode($search ?? '') ?>" id="messageForm" onsubmit="return handleSendMessage(event)">
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id ?? '') ?>">
                    <textarea name="noi_dung" id="noi_dung" placeholder="Nhập tin nhắn..." required></textarea>
                    <button type="submit" class="nut-gui" id="sendBtn"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        <?php else: ?>
            <p>Chọn một cuộc trò chuyện để xem chi tiết.</p>
        <?php endif; ?>
    </div>
</div>

<script>
// Supabase configuration từ PHP
const SUPABASE_URL = 'https://acddbjalchiruigappqg.supabase.co';
const SUPABASE_ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImFjZGRiamFsY2hpcnVpZ2FwcHFnIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTkwMzAzMTQsImV4cCI6MjA3NDYwNjMxNH0.Psefs-9-zIwe8OjhjQOpA19MddU3T9YMcfFtMcYQQS4';

// Khởi tạo Supabase client
const supabaseClient = supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

let lastMessageId = <?= $last_message_id_js ?>;
let currentUserId = <?= !empty($user_id) ? (int)$user_id : 0 ?>;
let adminId = 1; // Admin ID
let messageSubscription = null;
let chatSubscription = null;
let isScrolledToBottom = true;

function submitSearch(event) {
    event.preventDefault();
    const form = document.getElementById('searchForm');
    const searchValue = document.getElementById('searchInput').value.trim();
    form.submit();
}

//  NÚT "Tải lại" = XÓA TÌM KIẾM & HIỂN THỊ TẤT CẢ
function reloadPage() {
    window.location.href = 'index.php?c=message&a=index&filter=<?= $filter ?? "all" ?>';
}

function validateForm() {
    const noiDung = document.getElementById('noi_dung').value.trim();
    if (noiDung === '') {
        alert('Vui lòng nhập nội dung tin nhắn!');
        return false;
    }
    return true;
}

// Kiểm tra xem đã scroll đến cuối chưa
function checkScrollPosition() {
    const khungTinNhan = document.querySelector('.khung-tin-nhan');
    if (!khungTinNhan) return;
    const threshold = 100;
    isScrolledToBottom = (khungTinNhan.scrollHeight - khungTinNhan.scrollTop - khungTinNhan.clientHeight) < threshold;
}

// Scroll xuống cuối
function scrollToBottom() {
    const khungTinNhan = document.querySelector('.khung-tin-nhan');
    if (khungTinNhan) {
        khungTinNhan.scrollTop = khungTinNhan.scrollHeight;
    }
}

// Thêm tin nhắn mới vào UI
function addMessageToUI(msg) {
    const khungTinNhan = document.querySelector('.khung-tin-nhan');
    if (!khungTinNhan) return;
    
    const messageDiv = document.createElement('div');
    messageDiv.className = 'tin-nhan ' + (msg.ma_nguoi_gui == 1 ? 'admin' : 'nguoi-dung');
    
    const avatarHTML = msg.users && msg.users.avatar 
        ? `<img src="${escapeHtml(msg.users.avatar)}" alt="Avatar" class="hinh-avatar">`
        : `<div class="icon-avatar"><i class="fas fa-user"></i></div>`;
    
    const userName = msg.users ? (msg.users.ten_nguoi_dung || (msg.ma_nguoi_gui == 1 ? 'Admin' : 'User')) : (msg.ma_nguoi_gui == 1 ? 'Admin' : 'User');
    const timeStr = formatDateTime(msg.thoi_gian_gui);
    
    messageDiv.innerHTML = `
        ${avatarHTML}
        <div class="noi-dung-tin-nhan">
            <strong>${escapeHtml(userName)}:</strong>
            ${escapeHtml(msg.noi_dung)}<br>
            <small>${timeStr}</small>
        </div>
    `;
    
    khungTinNhan.appendChild(messageDiv);
    
    // Scroll xuống nếu đang ở cuối hoặc là tin nhắn của admin
    if (isScrolledToBottom || msg.ma_nguoi_gui == 1) {
        setTimeout(scrollToBottom, 100);
    }
}

// Lấy tin nhắn mới từ server (dùng khi cần fetch lại)
async function fetchNewMessages() {
    if (!currentUserId) return;
    
    try {
        const response = await fetch(`index.php?c=message&a=getNewMessages&user_id=${currentUserId}&last_message_id=${lastMessageId}`);
        const data = await response.json();
        
        if (data.error) {
            console.error('Error fetching messages:', data.error);
            return;
        }
        
        if (data.messages && data.messages.length > 0) {
            data.messages.forEach(msg => {
                addMessageToUI(msg);
            });
            lastMessageId = data.last_message_id;
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Cập nhật danh sách chat
async function updateChatList() {
    const search = encodeURIComponent('<?= htmlspecialchars($search ?? '') ?>');
    const filter = '<?= htmlspecialchars($filter ?? 'all') ?>';
    
    try {
        const response = await fetch(`index.php?c=message&a=getChatsUpdate&search=${search}&filter=${filter}`);
        const data = await response.json();
        
        if (data.error) {
            console.error('Error fetching chats:', data.error);
            return;
        }
        
        // Cập nhật số lượng trong filter links
        const allLink = document.querySelector('.loc a[href*="filter=all"]');
        const unreadLink = document.querySelector('.loc a[href*="filter=unread"]');
        const readLink = document.querySelector('.loc a[href*="filter=read"]');
        
        if (allLink) allLink.textContent = `Tất cả (${data.total_all})`;
        if (unreadLink) unreadLink.textContent = `Chưa đọc (${data.total_unread})`;
        if (readLink) readLink.textContent = `Đã đọc (${data.total_read})`;
        
        // Cập nhật danh sách chat (chỉ nếu không có user được chọn)
        if (!currentUserId && data.chats) {
            renderChatList(data.chats);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Render danh sách chat
function renderChatList(chats) {
    const container = document.querySelector('.danh-sach-chat-container');
    if (!container) return;
    
    if (chats.length === 0) {
        container.innerHTML = '<p class="khong-co-ket-qua">Không có chat nào</p>';
        return;
    }
    
    const filter = '<?= htmlspecialchars($filter ?? 'all') ?>';
    const search = encodeURIComponent('<?= htmlspecialchars($search ?? '') ?>');
    
    container.innerHTML = chats.map(chat => {
        const unreadClass = (chat.unread_count || 0) > 0 ? 'chua-doc' : '';
        const avatarHTML = chat.avatar 
            ? `<img src="${escapeHtml(chat.avatar)}" alt="Avatar" class="hinh-avatar">`
            : `<div class="icon-avatar"><i class="fas fa-user"></i></div>`;
        const lastMsg = chat.last_message ? escapeHtml(chat.last_message.noi_dung) : '';
        const updateTime = formatDateTime(chat.ngay_cap_nhat);
        
        return `
            <div class="item-chat ${unreadClass}" 
                 onclick="location.href='index.php?c=message&a=index&user_id=${chat.user_id}&filter=${filter}&search=${search}'">
                ${avatarHTML}
                <div class="thong-tin-chat">
                    <strong>${escapeHtml(chat.user_name)}</strong><br>
                    ${lastMsg ? `<small>${lastMsg}</small><br>` : ''}
                    <small>Cập nhật: ${updateTime}</small>
                </div>
            </div>
        `;
    }).join('');
}

// Escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Format datetime
function formatDateTime(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${day}.${month}.${year} ${hours}:${minutes}`;
}

// Lấy danh sách chat IDs liên quan đến user hiện tại
async function getChatIdsForUser(userId) {
    if (!userId) return [];
    
    try {
        const response = await fetch(`index.php?c=message&a=getChatsUpdate`);
        const data = await response.json();
        if (data.error || !data.chats) return [];
        
        // Tìm chat IDs của user hiện tại
        const userChats = data.chats.filter(c => c.user_id == userId);
        // Lấy chat IDs từ tất cả chats (cần query từ API để lấy ma_chat)
        const allChats = await supabaseClient
            .from('chats')
            .select('ma_chat')
            .or(`and(ma_nguoi_dung_1.eq.${userId},ma_nguoi_dung_2.eq.${adminId}),and(ma_nguoi_dung_1.eq.${adminId},ma_nguoi_dung_2.eq.${userId})`);
        
        if (allChats.error) return [];
        return allChats.data.map(c => c.ma_chat);
    } catch (error) {
        console.error('Error getting chat IDs:', error);
        return [];
    }
}

// Lưu trữ chat IDs của user hiện tại
let currentUserChatIds = [];

// Bắt đầu Realtime subscriptions
async function startRealtimeSubscriptions() {
    // Lấy chat IDs của user hiện tại nếu có
    if (currentUserId) {
        currentUserChatIds = await getChatIdsForUser(currentUserId);
    }
    
    // Subscribe vào chat_messages để lắng nghe tin nhắn mới
    // Subscribe tất cả tin nhắn và filter ở client side
    messageSubscription = supabaseClient
        .channel('chat-messages')
        .on('postgres_changes', {
            event: 'INSERT',
            schema: 'public',
            table: 'chat_messages'
        }, (payload) => {
            // Chỉ xử lý nếu đang xem chat và tin nhắn thuộc chat hiện tại
            if (currentUserId && currentUserChatIds.includes(payload.new.ma_chat)) {
                handleNewMessage(payload.new);
            }
            // Luôn cập nhật danh sách chat khi có tin nhắn mới
            updateChatList();
        })
        .subscribe();
    
    // Subscribe vào chats để cập nhật danh sách khi có thay đổi
    chatSubscription = supabaseClient
        .channel('chats-update')
        .on('postgres_changes', {
            event: '*', // Lắng nghe tất cả events (INSERT, UPDATE, DELETE)
            schema: 'public',
            table: 'chats'
        }, (payload) => {
            // Kiểm tra xem chat có liên quan đến admin không
            const chat = payload.new || payload.old;
            if (chat && (chat.ma_nguoi_dung_1 == adminId || chat.ma_nguoi_dung_2 == adminId)) {
                // Cập nhật danh sách chat khi có thay đổi
                updateChatList();
                
                // Nếu đang xem chat của user này, refresh chat IDs
                if (currentUserId) {
                    getChatIdsForUser(currentUserId).then(ids => {
                        currentUserChatIds = ids;
                    });
                }
            }
        })
        .subscribe();
}

// Xử lý tin nhắn mới từ Realtime
async function handleNewMessage(newMessage) {
    // Kiểm tra xem tin nhắn này có thuộc chat hiện tại không
    if (!currentUserId) return;
    
    // Lấy thông tin user của tin nhắn
    const { data: userData } = await supabaseClient
        .from('users')
        .select('id, ten_nguoi_dung, avatar')
        .eq('id', newMessage.ma_nguoi_gui)
        .single();
    
    // Format message để match với format hiện tại
    const formattedMessage = {
        ma_tin_nhan: newMessage.ma_tin_nhan,
        ma_chat: newMessage.ma_chat,
        ma_nguoi_gui: newMessage.ma_nguoi_gui,
        noi_dung: newMessage.noi_dung,
        thoi_gian_gui: newMessage.thoi_gian_gui,
        users: userData || { ten_nguoi_dung: newMessage.ma_nguoi_gui == adminId ? 'Admin' : 'User', avatar: null }
    };
    
    // Kiểm tra xem tin nhắn này có mới hơn lastMessageId không
    if (newMessage.ma_tin_nhan > lastMessageId) {
        addMessageToUI(formattedMessage);
        lastMessageId = newMessage.ma_tin_nhan;
        
        // Đánh dấu đã đọc nếu là tin nhắn từ user
        if (newMessage.ma_nguoi_gui != adminId) {
            // Call API để đánh dấu đã đọc
            fetch(`index.php?c=message&a=getNewMessages&user_id=${currentUserId}&last_message_id=${lastMessageId - 1}`)
                .catch(err => console.error('Error marking as read:', err));
        }
        
        // Cập nhật danh sách chat
        updateChatList();
    }
}

// Dừng Realtime subscriptions
function stopRealtimeSubscriptions() {
    if (messageSubscription) {
        supabaseClient.removeChannel(messageSubscription);
        messageSubscription = null;
    }
    if (chatSubscription) {
        supabaseClient.removeChannel(chatSubscription);
        chatSubscription = null;
    }
}

// Khởi tạo khi trang load
document.addEventListener('DOMContentLoaded', async function() {
    const khungTinNhan = document.querySelector('.khung-tin-nhan');
    if (khungTinNhan) {
        // Scroll xuống cuối khi load
        setTimeout(scrollToBottom, 100);
        
        // Theo dõi scroll
        khungTinNhan.addEventListener('scroll', checkScrollPosition);
    }
    
    // Bắt đầu Realtime subscriptions
    await startRealtimeSubscriptions();
    
    // Cập nhật danh sách chat ban đầu
    updateChatList();
    
    // Dừng subscriptions khi rời khỏi trang
    window.addEventListener('beforeunload', stopRealtimeSubscriptions);
    
    // Refresh chat IDs khi user chọn chat khác (khi click vào chat item)
    document.addEventListener('click', function(e) {
        const chatItem = e.target.closest('.item-chat');
        if (chatItem) {
            // Delay một chút để URL thay đổi trước
            setTimeout(async () => {
                // Lấy user_id từ URL mới
                const urlParams = new URLSearchParams(window.location.search);
                const newUserId = parseInt(urlParams.get('user_id') || '0');
                if (newUserId !== currentUserId) {
                    currentUserId = newUserId;
                    if (currentUserId) {
                        currentUserChatIds = await getChatIdsForUser(currentUserId);
                    } else {
                        currentUserChatIds = [];
                    }
                }
            }, 100);
        }
    });
});

// Xử lý gửi tin nhắn bằng AJAX
function handleSendMessage(event) {
    event.preventDefault();
    
    if (!validateForm()) {
        return false;
    }
    
    const form = document.getElementById('messageForm');
    const formData = new FormData(form);
    const sendBtn = document.getElementById('sendBtn');
    const textarea = document.getElementById('noi_dung');
    const originalBtnText = sendBtn.innerHTML;
    
    // Disable button
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Xóa nội dung textarea
            textarea.value = '';
            
            // Realtime sẽ tự động cập nhật tin nhắn mới
            // Chỉ cần enable lại button
            sendBtn.disabled = false;
            sendBtn.innerHTML = originalBtnText;
        } else {
            alert(data.message || 'Có lỗi xảy ra khi gửi tin nhắn.');
            sendBtn.disabled = false;
            sendBtn.innerHTML = originalBtnText;
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        sendBtn.disabled = false;
        sendBtn.innerHTML = originalBtnText;
        alert('Có lỗi xảy ra khi gửi tin nhắn. Vui lòng thử lại.');
    });
    
    return false;
}

if (window.history.replaceState) {
    window.history.replaceState(null, document.title, window.location.href);
}
</script>
</body>
</html>