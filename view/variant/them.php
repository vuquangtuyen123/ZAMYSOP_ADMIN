<link rel="stylesheet" href="assets/css/variant.css">
<?php include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="assets/css/pagination.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/variant.js"></script>

<main class="noi-dung-chinh">
    <div class="thanh-tieu-de">
        <h2>Thêm biến thể sản phẩm</h2>
        <a href="index.php?c=variant&a=index" class="reset-btn">Quay lại</a>
    </div>

    <?php if (!empty($error)): ?>
        <div style="background:#f8d7da; color:#721c24; padding:15px; border-radius:8px; margin-bottom:20px;">
            <i class="fa fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="form-them">
        <div class="form-row">
            <label>Sản phẩm <span style="color:red">*</span></label>
            <select name="ma_san_pham" required>
                <option value="">-- Chọn sản phẩm --</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['ma_san_pham'] ?>"><?= htmlspecialchars($p['ten_san_pham']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <label>Màu sắc <span style="color:red">*</span></label>
            <select name="ma_mau" required>
                <option value="">-- Chọn màu --</option>
                <?php foreach ($colors as $c): ?>
                    <option value="<?= $c['ma_mau'] ?>"><?= htmlspecialchars($c['ten_mau']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <label>Size <span style="color:red">*</span></label>
            <select name="ma_size" required>
                <option value="">-- Chọn size --</option>
                <?php foreach ($sizes as $s): ?>
                    <option value="<?= $s['ma_size'] ?>"><?= htmlspecialchars($s['ten_size']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <label>Tồn kho</label>
           <input type="number" name="ton_kho" min="1" required oninput="if(this.value < 1) this.value = 1;">

        </div>

        <div class="form-row">
            <button type="submit" class="btn-luu">Thêm mới</button>
            <a href="index.php?c=variant&a=index" class="btn-dong">Hủy</a>
        </div>
    </form>
</main>