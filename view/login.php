<!--
    Giao diện đăng nhập hệ thống Admin
    
    Tệp này chứa form đăng nhập với các tính năng:
    - Form nhập email và mật khẩu
    - Hiển thị/ẩn mật khẩu
    - Hiển thị thông báo lỗi
    - Responsive design
    - Branding cho Zamy Shop
    
    @author Đội phát triển
    @version 1.0
-->
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Admin - Zamy Shop</title>
    
    <!-- CSS cho giao diện đăng nhập -->
    <link rel="stylesheet" href="assets/css/login.css">
    
    <!-- Font Awesome icons để hiển thị icon mắt show/hide password -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Container chính chứa toàn bộ form đăng nhập -->
    <div class="login-container">
        <div class="login-box">
            <!-- Logo và tên thương hiệu -->
            <div class="logo">
                <h1>Zamy Shop</h1>
                <p>La Vie en Rose</p>
            </div>
            
            <!-- Hiển thị thông báo lỗi nếu đăng nhập thất bại -->
            <?php if (!empty($error)): ?>
                <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            
            <!-- Form đăng nhập -->
            <!-- Action sẽ gửi dữ liệu đến LoginController->login() -->
            <form method="POST" action="index.php?c=login&a=login">
                <!-- Input email -->
                <input type="email" 
                       name="email" 
                       placeholder="Email" 
                       required 
                       autocomplete="email"
                       aria-label="Địa chỉ email">
                
                <!-- Container cho input password và nút show/hide -->
                <div class="password-container">
                    <input type="password" 
                           name="password" 
                           id="password" 
                           placeholder="Mật khẩu" 
                           required 
                           autocomplete="current-password"
                           aria-label="Mật khẩu">
                    
                    <!-- Nút show/hide password -->
                    <span class="toggle-password" 
                          onclick="togglePassword()" 
                          role="button" 
                          tabindex="0"
                          aria-label="Hiển thị/ẩn mật khẩu">
                        <i class="fas fa-eye-slash"></i>
                    </span>
                </div>
                
                <!-- Nút submit đăng nhập -->
                <button type="submit">Đăng nhập</button>
            </form>
            
            <!-- Thông tin campaign/khuyến mãi -->
            <div class="campaign">Summer Campaign 2025</div>
        </div>
    </div>
    
    <!-- JavaScript xử lý hiển thị/ẩn mật khẩu -->
    <script>
        /**
         * Hàm toggle hiển thị/ẩn mật khẩu
         * 
         * Chuyển đổi giữa type="password" và type="text" của input
         * Đồng thời thay đổi icon mắt tương ứng
         */
        function togglePassword() {
            const password = document.getElementById("password");
            const toggle = document.querySelector(".toggle-password i");
            
            if (password.type === "password") {
                // Hiển thị mật khẩu
                password.type = "text";
                toggle.className = "fas fa-eye"; // Icon mắt mở
            } else {
                // Ẩn mật khẩu
                password.type = "password";
                toggle.className = "fas fa-eye-slash"; // Icon mắt đóng
            }
        }
        
        // Thêm sự kiện keyboard cho accessibility
        document.querySelector('.toggle-password').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                togglePassword();
            }
        });
    </script>
</body>
</html>

<!--
    Ghi chú về bảo mật và UX:
    
    1. Form Security:
       - Sử dụng method="POST" để bảo mật dữ liệu
       - htmlspecialchars() để tránh XSS khi hiển thị lỗi
       - autocomplete attributes cho password manager
       
    2. Accessibility:
       - aria-label cho screen readers
       - tabindex và keyboard events cho toggle password
       - proper semantic HTML
       
    3. User Experience:
       - Toggle password visibility
       - Clear error messages
       - Responsive design
       - Loading states (có thể thêm)
       
    4. Branding:
       - Logo và tên thương hiệu
       - Campaign information
       - Consistent color scheme
       - php -S localhost:3000 -t D:\Doan_admin\public
       - echo "Stopping server..." && taskkill /F /IM php.exe
-->