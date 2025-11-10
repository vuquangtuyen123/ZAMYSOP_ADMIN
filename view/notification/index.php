<?php include __DIR__ . '/../menu.php'; ?>
<link rel="stylesheet" href="assets/css/dashboard-tiengviet.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="assets/js/dashboard.js"></script>
    <title>Thông báo - ZamyShop</title>
<div class="khung-dashboard">
    
    <main class="noi-dung-chinh">
        <div class="trang-container">
            <h1 class="trang-tieu-de">Thông báo</h1>
            
            <?php if (empty($notifications)): ?>
                <div class="empty-state">
                    <i class="fas fa-bell-slash" style="font-size: 48px; color: #ccc; margin-bottom: 16px;"></i>
                    <p style="color: #999;">Chưa có thông báo nào</p>
                </div>
            <?php else: ?>
                <div class="notification-list">
                    <?php foreach ($notifications as $notif): ?>
                        <div class="notification-item <?= $notif['da_doc'] ? '' : 'unread' ?>" 
                             data-id="<?= $notif['ma_thong_bao'] ?>">
                            <div class="notification-icon">
                                <?php if ($notif['loai_thong_bao'] == 'order'): ?>
                                    <i class="fas fa-shopping-cart"></i>
                                <?php elseif ($notif['loai_thong_bao'] == 'system'): ?>
                                    <i class="fas fa-envelope"></i>
                                <?php else: ?>
                                    <i class="fas fa-bell"></i>
                                <?php endif; ?>
                            </div>
                            <div class="notification-content">
                                <h3><?= htmlspecialchars($notif['tieu_de'] ?? 'Thông báo') ?></h3>
                                <p><?= htmlspecialchars($notif['noi_dung'] ?? '') ?></p>
                                <span class="notification-time">
                                    <?php 
                                    $time = strtotime($notif['thoi_gian_tao'] ?? 'now');
                                    echo date('d/m/Y H:i', $time);
                                    ?>
                                </span>
                            </div>
                            <div class="notification-actions">
                                <?php if (!$notif['da_doc']): ?>
                                    <button class="btn-mark-read" data-id="<?= $notif['ma_thong_bao'] ?>" title="Đánh dấu đã đọc">
                                        <i class="fas fa-check"></i>
                                    </button>
                                <?php endif; ?>
                                <button class="btn-delete" data-id="<?= $notif['ma_thong_bao'] ?>" title="Xóa thông báo">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<style>
.notification-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.notification-item {
    background: white;
    border-radius: 8px;
    padding: 16px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: all 0.2s;
}

.notification-item:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.notification-item.unread {
    background: #f0f7ff;
    border-left: 3px solid #3498db;
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e8f4f8;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3498db;
    flex-shrink: 0;
}

.notification-content {
    flex: 1;
}

.notification-content h3 {
    font-size: 15px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 4px;
}

.notification-content p {
    font-size: 14px;
    color: #666;
    margin-bottom: 8px;
    line-height: 1.4;
}

.notification-time {
    font-size: 12px;
    color: #999;
}

.notification-actions {
    display: flex;
    gap: 8px;
}

.btn-mark-read {
    background: #3498db;
    color: white;
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.btn-mark-read:hover {
    background: #2980b9;
}

.btn-delete {
    background: #e74c3c;
    color: white;
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.btn-delete:hover {
    background: #c0392b;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}
</style>

<script>
document.querySelectorAll('.btn-mark-read').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        fetch('index.php?c=notification&a=markAsRead&id=' + id, {
            method: 'POST'
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const item = this.closest('.notification-item');
                item.classList.remove('unread');
                this.remove();
                location.reload();
            }
        });
    });
});

document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        if (!confirm('Bạn có chắc muốn xóa thông báo này?')) {
            return;
        }
        
        fetch('index.php?c=notification&a=delete&id=' + id, {
            method: 'POST'
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const item = this.closest('.notification-item');
                item.style.transition = 'opacity 0.3s';
                item.style.opacity = '0';
                setTimeout(() => {
                    item.remove();
                    // Nếu không còn thông báo nào, reload để hiển thị empty state
                    if (document.querySelectorAll('.notification-item').length === 0) {
                        location.reload();
                    }
                }, 300);
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể xóa thông báo'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Lỗi kết nối server.');
        });
    });
});
</script>

