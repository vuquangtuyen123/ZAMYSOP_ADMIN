<?php include __DIR__ . '/menu.php'; ?>
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="assets/css/auth.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<main class="noi-dung-chinh auth-container">
    <div class="noi-dung-dashboard">
        <h3><i class="fas fa-key"></i> Đổi mật khẩu</h3>
        <p class="auth-note">Tài khoản: <strong><?= htmlspecialchars($email ?? '') ?></strong></p>
        <?php if (!empty($error)): ?>
            <div style="color:#dc3545; margin-bottom:10px;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" class="form-them">
            <div class="form-row"><label>Mật khẩu mới</label><input type="password" name="new_password" required></div>
            <div class="form-row"><label>Nhập lại mật khẩu</label><input type="password" name="confirm_password" required></div>
            <div class="form-actions">
                <button type="submit" class="them-moi-btn"><i class="fas fa-save"></i> Lưu</button>
                <a href="index.php?c=login&a=login" class="all-btn">Hủy</a>
            </div>
        </form>
    </div>
</main>

