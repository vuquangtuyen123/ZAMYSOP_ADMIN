<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập Admin</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="logo">
                <h1>Zamy Shop</h1>
                <p>La Vie en Rose</p>
            </div>
            <?php if (!empty($error)): ?>
                <p class="error"><?= $error ?></p>
            <?php endif; ?>
            <form method="POST" action="index.php?c=AdminLoginController&a=login">
                <input type="email" name="email" placeholder="Email" required>
                <div class="password-container">
                    <input type="password" name="password" id="password" placeholder="Mật khẩu" required>
                    <span class="toggle-password" onclick="togglePassword()">
                        <i class="fas fa-eye-slash"></i>
                    </span>
                </div>
                <button type="submit">Đăng nhập</button>
            </form>
            <div class="campaign">Summer Campaign 2025</div>
        </div>
    </div>
    <script>
        function togglePassword() {
            const password = document.getElementById("password");
            const toggle = document.querySelector(".toggle-password i");
            if (password.type === "password") {
                password.type = "text";
                toggle.className = "fas fa-eye";
            } else {
                password.type = "password";
                toggle.className = "fas fa-eye-slash";
            }
        }
    </script>
</body>
</html>