<?php include __DIR__ . '/../menu.php'; ?>
<head>
    <meta charset="UTF-8">
    <title>Người dùng</title>
    <link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
    <link rel="stylesheet" href="assets/css/user.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/user.js"></script>
</head>

<main class="noi-dung-chinh">

    <!-- ===== THANH TIÊU ĐỀ + LỌC ===== -->
    <header class="thanh-tieu-de">
        <div class="hop-tim-kiem">
            <form method="GET" action="index.php">
                <input type="hidden" name="c" value="user">
                <input type="hidden" name="a" value="index">

                <input type="text" name="search"
                       value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                       placeholder="🔍 Tìm theo tên người dùng"
                       aria-label="Tìm kiếm">

                <select name="role" onchange="this.form.submit()">
                    <option value="0" <?= (($_GET['role'] ?? '') == 0 ? 'selected' : '') ?>>Tất cả vai trò</option>
                    <option value="1" <?= (($_GET['role'] ?? '') == 1 ? 'selected' : '') ?>>Administrator</option>
                    <option value="2" <?= (($_GET['role'] ?? '') == 2 ? 'selected' : '') ?>>Moderator</option>
                    <option value="3" <?= (($_GET['role'] ?? '') == 3 ? 'selected' : '') ?>>User</option>
                </select>

                <button type="submit" title="Tìm kiếm">
                </button>
            </form>
        </div>

        <div class="thong-tin-nguoi-dung">
            <?php if (can('user.manage_staff_and_customers')): ?>
                <a href="index.php?c=user&a=them" class="them-moi-btn">
                    <i class="fas fa-plus"></i> Thêm mới
                </a>
            <?php endif; ?>
        </div>
    </header>

    <!-- ===== DANH SÁCH NGƯỜI DÙNG ===== -->
    <div class="noi-dung-dashboard">
        <table class="news-list">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Email</th>
                    <th>SĐT</th>
                    <th>Vai trò</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['id']) ?></td>
                            <td><?= htmlspecialchars($u['ten_nguoi_dung'] ?? '') ?></td>
                            <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
                            <td><?= htmlspecialchars($u['so_dien_thoai'] ?? '') ?></td>
                            <td>
                                <?= (int)($u['ma_role'] ?? 3) === 1 ? 'Administrator' :
                                    ((int)($u['ma_role'] ?? 3) === 2 ? 'Moderator' : 'User') ?>
                            </td>
                            <td>
                                <?php if (can('user.manage_staff_and_customers')): ?>
                                    <a href="index.php?c=user&a=sua&id=<?= $u['id'] ?>" class="action-link edit-link">
                                        <i class="fas fa-edit"></i> Sửa
                                    </a>
                                    <a href="index.php?c=user&a=xoa&id=<?= $u['id'] ?>" 
                                       class="action-link delete-link" 
                                       onclick="return confirm('Bạn có chắc muốn xóa người dùng này không?')">
                                        <i class="fas fa-trash"></i> Xóa
                                    </a>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center;">Không có người dùng nào</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ===== PHÂN TRANG ===== -->
    <?php if (!empty($totalPages) && $totalPages > 1): ?>
        <div class="user-pagination-wrapper">
            <div class="user-pagination">
                <?php 
                    $cur = $page ?? 1; 
                    $prev = max(1, $cur - 1); 
                    $next = min($totalPages, $cur + 1);
                    $search = trim($_GET['search'] ?? '');
                    $role = (int)($_GET['role'] ?? 0);
                    $extra = ($search ? '&search=' . urlencode($search) : '') . ($role ? '&role=' . $role : '');
                ?>
                <a href="index.php?c=user&a=index&page=<?= $prev . $extra ?>" class="<?= $cur == 1 ? 'disabled' : '' ?>">&lt;</a>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="index.php?c=user&a=index&page=<?= $i . $extra ?>" class="<?= $i == $cur ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <a href="index.php?c=user&a=index&page=<?= $next . $extra ?>" class="<?= $cur == $totalPages ? 'disabled' : '' ?>">&gt;</a>
            </div>
        </div>
    <?php endif; ?>

</main>
