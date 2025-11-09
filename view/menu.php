<?php 
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../model/notification_model.php';
require_once __DIR__ . '/../model/message_model.php';
$unreadCount = NotificationModel::getUnreadCount(1); // Admin ID = 1
$messageModel = new MessageModel();
$unreadMessageCount = 0;
try {
    $chats = $messageModel->getChatsByAdminWithSearch(1, '', 'unread');
    // Đếm số lượng người chưa đọc (mỗi chat = 1 người), không phải tổng số tin nhắn
    foreach ($chats as $chat) {
        if ((int)($chat['unread_count'] ?? 0) > 0) {
            $unreadMessageCount++;
        }
    }
} catch (Exception $e) {
    error_log("Error getting unread message count: " . $e->getMessage());
}
?>
<!-- Thanh Menu Bên Trái (Sidebar) -->
<aside class="thanh-menu-ben">
    <!-- Logo thương hiệu -->
    <div class="logo">
        <h2>ZAMY SHOP</h2>
    </div>
    
    <!-- Menu điều hướng chính -->
    <nav class="menu-dieu-huong">
        <ul>
            <!-- Nhóm menu: TỔNG QUAN -->
            <li class="nhom-menu">
                <div class="tieu-de-nhom">TỔNG QUAN</div>
                
                <!-- Dashboard -->
               <!--  <li><a href="index.php?c=login&a=dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li> -->
                 <li><a href="index.php?c=dashboard&a=index"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>

                
                <!-- Menu Sản phẩm với submenu -->
                <li class="muc-menu">
                    <span class="nut-mo-dong" data-target="menu-san-pham">
                        <i class="fas fa-box"></i> Sản phẩm 
                        <i class="fas fa-chevron-down icon-mo-dong"></i>
                    </span>
                    <!-- Submenu sản phẩm -->
					<ul class="menu-con" id="menu-san-pham">
						<li><a href="index.php?c=product&a=index"><i class="fas fa-list"></i> Tất cả sản phẩm</a></li>
						<li><a href="index.php?c=danhmuc&a=index"><i class="fas fa-sitemap"></i> Danh mục</a></li>
                         <li><a href="index.php?c=variant&a=index"><i class="fas fa-th-large"></i> Biến thể sản phẩm</a></li>
                         <li><a href="index.php?c=color&a=index"><i class="fas fa-palette"></i> Màu sắc</a></li>
						<li><a href="index.php?c=size&a=index"><i class="fas fa-text-height"></i> Size</a></li>
				
						<li><a href="index.php?c=collection&a=index"><i class="fas fa-layer-group"></i> Bộ sưu tập</a></li>
                
					</ul>
                </li>
                
                <!-- Menu Đơn hàng với submenu -->
                <li class="muc-menu">
                    <span class="nut-mo-dong" data-target="menu-don-hang">
                        <i class="fas fa-shopping-cart"></i> Đơn hàng 
                        <i class="fas fa-chevron-down icon-mo-dong"></i>
                    </span>
                    <!-- Submenu đơn hàng -->
                    <ul class="menu-con" id="menu-don-hang">
                        <li><a href="index.php?c=order&a=index"><i class="fas fa-list"></i> Tất cả đơn hàng</a></li>
                        
                        <li><a href="index.php?c=order&a=processing"><i class="fas fa-cogs"></i> Đang xử lý</a></li>
                        <li><a href="index.php?c=order&a=completed"><i class="fas fa-check"></i> Hoàn thành</a></li>
                    </ul>
                </li>
                
                <!-- Menu items không có submenu -->
                <?php if (can('user.manage_staff_and_customers') || can('user.view_customers')): ?>
                <li><a href="index.php?c=user&a=index"><i class="fas fa-users"></i> Người dùng</a></li>
                <?php endif; ?>
                <?php if (can('discount.crud') || can('discount.create_edit')): ?>
                <li><a href="index.php?c=coupon&a=index"><i class="fas fa-tags"></i> Mã giảm giá</a></li>
                <?php endif; ?>
                <?php if (can('inventory.upload')): ?>
                <li><a href="index.php?c=inventory&a=index"><i class="fas fa-warehouse"></i> Quản lý kho</a></li>
                <?php endif; ?>
            </li>
            
            <!-- Nhóm menu: GIAO TIẾP -->
            <li class="nhom-menu">
                <div class="tieu-de-nhom">GIAO TIẾP</div>
                <?php if (can('news_banner.crud') || can('news_banner.create_edit')): ?>
                <li><a href="index.php?c=news&a=index"><i class="fas fa-newspaper"></i> Tin tức</a></li>
                <li><a href="index.php?c=banner&a=index"><i class="fas fa-flag"></i> Banner</a></li>
                <?php endif; ?>
                <?php if (can('comment.moderate') || can('comment.reply')): ?>
                <li><a href="index.php?c=comment&a=index"><i class="fas fa-comments"></i> Bình luận</a></li>
                <?php endif; ?>
                <?php if (can('message.manage_all') || can('message.reply')): ?>
                <li>
                    <a href="index.php?c=message&a=index">
                        <i class="fas fa-envelope"></i> Tin nhắn 
                        <span class="notification-badge" id="message-count"><?= $unreadMessageCount > 0 ? $unreadMessageCount : '' ?></span>
                    </a>
                </li>
                <?php endif; ?>
                <li>
                    <a href="index.php?c=notification&a=index">
                        <i class="fas fa-bell"></i> Thông báo
                        <span class="notification-badge" id="notification-count"><?= $unreadCount > 0 ? $unreadCount : '' ?></span>
                    </a>
                </li>
            </li>
            
            <!-- Nhóm menu: HỆ THỐNG -->
            <li class="nhom-menu">
                <div class="tieu-de-nhom">HỆ THỐNG</div>
                <?php if (can('product.crud')): ?>
                <li><a href="index.php?c=payment_settings&a=index"><i class="fas fa-credit-card"></i> Cài đặt thanh toán</a></li>
                <?php endif; ?>
            </li>
        </ul>
        
        <!-- Nút Đăng Xuất ở cuối sidebar -->
        <div class="nut-dang-xuat-menu">
            <a href="index.php?c=login&a=logout" class="dang-xuat">
                <i class="fas fa-sign-out-alt"></i> Đăng xuất
            </a>
        </div>
    </nav>
</aside>

<script>
// Realtime update cho notification và message count
(function() {
    function updateCounts() {
        fetch('index.php?c=dashboard&a=getCounts')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const notifBadge = document.getElementById('notification-count');
                    const messageBadge = document.getElementById('message-count');
                    
                    if (notifBadge) {
                        const count = data.notification_count || 0;
                        notifBadge.textContent = count > 0 ? count : '';
                    }
                    
                    if (messageBadge) {
                        const count = data.message_count || 0;
                        messageBadge.textContent = count > 0 ? count : '';
                    }
                }
            })
            .catch(error => {
                console.error('Error updating counts:', error);
            });
    }
    
    // Update mỗi 10 giây
    setInterval(updateCounts, 10000);
    
    // Update ngay khi load trang
    updateCounts();
})();
</script>