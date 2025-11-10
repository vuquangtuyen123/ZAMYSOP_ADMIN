<?php include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/size.css">
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="assets/css/pagination.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/size.js"></script>
<head><meta charset="UTF-8"><title>Quản lý Size -ZamyShop</title></head>
<main class="noi-dung-chinh">
	<header class="thanh-tieu-de">
		<div></div>
		<div><a href="index.php?c=size&a=them" class="them-moi-btn"><i class="fas fa-plus"></i> Thêm size</a></div>
	</header>
	<div class="noi-dung-dashboard">
		<table class="news-list">
			<thead><tr><th>Mã</th><th>Tên size</th><th>Hành động</th></tr></thead>
			<tbody>
			<?php foreach (($sizes ?? []) as $s): ?>
				<tr>
					<td><?= (int)$s['ma_size'] ?></td>
					<td><?= htmlspecialchars($s['ten_size']) ?></td>
					<td>
						<a class="action-link edit-link" href="index.php?c=size&a=sua&id=<?= (int)$s['ma_size'] ?>">Sửa</a>
						<?php if (can('product.crud')): ?>
    <a class="action-link delete-link" href="index.php?c=size&a=xoa&id=<?= (int)$s['ma_size'] ?>" onclick="return confirm('Xóa size này?')">Xóa</a>
<?php endif; ?>

					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<div class="pagination">
			<?php if (isset($totalPages) && $totalPages > 1): ?>
				<?php $prevPage = $page > 1 ? $page - 1 : 1; $nextPage = $page < $totalPages ? $page + 1 : $totalPages; ?>
				<a href="index.php?c=size&a=index&page=<?= $prevPage ?>" class="<?= $page <= 1 ? 'disabled' : '' ?>">&lt;</a>
				<?php for ($i = 1; $i <= $totalPages; $i++): ?>
					<a href="index.php?c=size&a=index&page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
				<?php endfor; ?>
				<a href="index.php?c=size&a=index&page=<?= $nextPage ?>" class="<?= $page >= $totalPages ? 'disabled' : '' ?>">&gt;</a>
			<?php endif; ?>
		</div>
	</div>
</main>


