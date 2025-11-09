<?php include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/user.css">
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/user.js"></script>

<main class="noi-dung-chinh">
    <div class="noi-dung-dashboard">
        <h3>Thêm người dùng</h3>
        <form method="POST" action="index.php?c=user&a=them" class="form-them">
            <div class="form-row">
                <label>Tên</label>
                <input type="text" name="ten_nguoi_dung" required>
            </div>

            <div class="form-row">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-row">
                <label>SĐT</label>
                <input type="text" name="so_dien_thoai" maxlength="10" pattern="[0-9]{10}" 
                       title="Vui lòng nhập đúng 10 chữ số" required>
            </div>

            <div class="form-row">
                <label>Role</label>
                <select name="ma_role">
                    <option value="1">Administrator</option>
                    <option value="2">Moderator</option>
                    <option value="3">User</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="them-moi-btn">
                    <i class="fas fa-save"></i> Lưu
                </button>
                <a href="index.php?c=user&a=index" class="all-btn">Hủy</a>
            </div>
        </form>
    </div>
</main>
