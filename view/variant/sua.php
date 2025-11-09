<link rel="stylesheet" href="assets/css/variant.css">
<?php include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="assets/css/pagination.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/variant.js"></script>

<div class="noi-dung-chinh">
  <div class="thanh-tieu-de">
    <h2>Sửa biến thể</h2>
  </div>

  <?php if (!empty($_GET['error'])): ?>
    <div style="background:#f8d7da;color:#721c24;padding:12px;border-radius:6px;margin-bottom:15px;">
      <i class="fa fa-exclamation-circle"></i> <?= htmlspecialchars($_GET['error']) ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="form-group">
      <label>Sản phẩm:</label>
      <select name="ma_san_pham" disabled>
        <?php foreach ($products as $p): ?>
          <option value="<?= $p['ma_san_pham'] ?>" 
            <?= (isset($variant['ma_san_pham']) && $p['ma_san_pham'] == $variant['ma_san_pham']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($p['ten_san_pham']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label>Màu:</label>
      <select name="ma_mau" disabled>
        <?php foreach ($colors as $c): ?>
          <option value="<?= $c['ma_mau'] ?>"
            <?= (isset($variant['ma_mau']) && $c['ma_mau'] == $variant['ma_mau']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['ten_mau']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label>Size:</label>
      <select name="ma_size" disabled>
        <?php foreach ($sizes as $s): ?>
          <option value="<?= $s['ma_size'] ?>"
            <?= (isset($variant['ma_size']) && $s['ma_size'] == $variant['ma_size']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($s['ten_size']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="ton_kho">Tồn kho:</label>
      <input type="number" id="ton_kho" name="ton_kho" 
             value="<?= htmlspecialchars($variant['ton_kho'] ?? 0) ?>" 
             min="0" 
             oninput="this.value=this.value.replace(/[^0-9]/g,'')"
             required>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn-luu">Lưu</button>
      <a href="index.php?c=variant&a=index" class="btn-dong">Hủy</a>
    </div>
  </form>
</div>
