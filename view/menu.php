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
                <li><a href="index.php?c=login&a=dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                
                <!-- Menu Sản phẩm với submenu -->
                <li class="muc-menu">
                    <span class="nut-mo-dong" data-target="menu-san-pham">
                        <i class="fas fa-box"></i> Sản phẩm 
                        <i class="fas fa-chevron-down icon-mo-dong"></i>
                    </span>
                    <!-- Submenu sản phẩm -->
                    <ul class="menu-con" id="menu-san-pham">
                        <li><a href="index.php?c=product&a=index"><i class="fas fa-list"></i> Tất cả sản phẩm</a></li>
                    
                        <li><a href="index.php?c=category&a=index"><i class="fas fa-sitemap"></i> Danh mục</a></li>
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
                <li><a href="index.php?c=user&a=index"><i class="fas fa-users"></i> Người dùng</a></li>
                <li><a href="index.php?c=coupon&a=index"><i class="fas fa-tags"></i> Mã giảm giá</a></li>
                <li><a href="index.php?c=inventory&a=index"><i class="fas fa-warehouse"></i> Quản lý kho</a></li>
            </li>
            
            <!-- Nhóm menu: GIAO TIẾP -->
            <li class="nhom-menu">
                <div class="tieu-de-nhom">GIAO TIẾP</div>
                <li><a href="index.php?c=news&a=index"><i class="fas fa-newspaper"></i> Tin tức</a></li>
                <li><a href="index.php?c=comment&a=index"><i class="fas fa-comments"></i> Bình luận</a></li>
                <li><a href="index.php?c=message&a=index"><i class="fas fa-envelope"></i> Tin nhắn</a></li>
                <li><a href="index.php?c=banner&a=index"><i class="fas fa-flag"></i> Banner</a></li>
            </li>
            
            <!-- Nhóm menu: HỆ THỐNG -->
            <li class="nhom-menu">
                <div class="tieu-de-nhom">HỆ THỐNG</div>
                <li><a href="index.php?c=setting&a=index"><i class="fas fa-cog"></i> Cài đặt</a></li>
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