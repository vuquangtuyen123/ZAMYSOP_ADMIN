<!--
    Giao diện Dashboard Admin của Zamy Shop
    
    Tệp này chứa giao diện chính của hệ thống quản trị với các tính năng:
    - Sidebar navigation với menu phân cấp
    - Header với search và thông tin user
    - Responsive layout
    - Dropdown menu với JavaScript
    - Font Awesome icons
    
    @author Đội phát triển
    @version 1.0
-->
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zamy Shop Admin Dashboard</title>
    
    <!-- CSS cho giao diện dashboard -->
    <link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
    
    <!-- Font Awesome cho icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js cho biểu đồ thống kê (sẽ dùng trong tương lai) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Dashboard JavaScript - Logic xử lý tương tác -->
    <script src="assets/js/dashboard.js"></script>
</head>
<body>
    <!-- Container chính của dashboard -->
    <div class="khung-dashboard">
        
        <!-- Include thanh menu có thể tái sử dụng -->
        <?php include __DIR__ . '/menu.php'; ?>

        <!-- Nội Dung Chính (Main Content Area) -->
        <main class="noi-dung-chinh">
            <!-- Header với search và thông tin user -->
            <header class="thanh-tieu-de">
                <!-- Ô tìm kiếm -->
                <div class="hop-tim-kiem">
                    <input type="text" placeholder="Tìm kiếm" aria-label="Tìm kiếm">
                    <i class="fas fa-search" style="cursor: pointer;" title="Tìm kiếm"></i>
                </div>
                
                <!-- Thông tin người dùng đang đăng nhập -->
                <div class="thong-tin-nguoi-dung">
                    <span>Xin chào: <?= htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['user_email']) ?></span>
                </div>
            </header>

            <!-- Khu vực nội dung dashboard - sẽ được mở rộng trong tương lai -->
            <div class="noi-dung-dashboard">
                <!-- 
                    Đây là nơi sẽ hiển thị:
                    - Thống kê tổng quan (số đơn hàng, doanh thu, sản phẩm...)
                    - Biểu đồ doanh thu theo thời gian
                    - Danh sách đơn hàng gần đây
                    - Thông báo hệ thống
                    - Và các widget khác
                -->
                <div class="container-thong-ke">
                    <h3>Dashboard đang được phát triển...</h3>
                    
                </div>
            </div>
        </main>
    </div>

</body>
</html>

<!--
    Ghi chú về cấu trúc và tính năng:
    
    1. Layout Structure:
       - Sidebar cố định bên trái với navigation menu
       - Main content area responsive bên phải
       - Header với search và user info
       
    2. Menu System:
       - Phân nhóm theo chức năng (Tổng quan, Giao tiếp, Hệ thống)
       - Dropdown submenu cho các module phức tạp
       - Icon từ Font Awesome cho UX tốt hơn
       
    3. JavaScript Features:
       - Dropdown menu với animation (dashboard.js)
       - Keyboard navigation support
       - Search functionality
       - Responsive behavior (sẽ mở rộng)
       
    4. Security:
       - htmlspecialchars() để tránh XSS
       - Session validation ở controller level
       
    5. File Organization:
       - CSS: assets/css/dashboard-tiengviet.css
       - JavaScript: assets/js/dashboard.js
       - Separation of concerns cho maintainability
       
    6. Future Enhancements:
       - Dashboard widgets với thống kê
       - Real-time notifications
       - Mobile responsive menu
       - Dark/light theme toggle
       - Advanced search functionality
-->