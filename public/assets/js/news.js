document.addEventListener('DOMContentLoaded', () => {
    let activeItem = null;

    // Chỉ click vào trường nội dung mới hiện chi tiết
    document.querySelectorAll('.news-content-cell').forEach(cell => {
        cell.addEventListener('click', (e) => {
            const item = cell.parentElement;
            if (activeItem) activeItem.classList.remove('active');
            item.classList.add('active');
            activeItem = item;

            const maTinTuc = item.dataset.maTinTuc;
            fetch(`index.php?c=news&a=detail&ma_tin_tuc=${maTinTuc}`)
                .then(response => response.text())
                .then(html => {
                    const modal = document.createElement('div');
                    modal.className = 'modal';
                    modal.innerHTML = html;
                    document.body.appendChild(modal);
                    modal.style.display = 'block';
                });
        });
    });

    // Xử lý đóng modal để xóa trạng thái active
    document.addEventListener('click', (e) => {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (e.target === modal || e.target.classList.contains('close-modal')) {
                modal.style.display = 'none';
                if (activeItem) activeItem.classList.remove('active');
                modal.remove();
            }
        });
    });

    // Toggle trạng thái hiển thị
    document.querySelectorAll('.toggle-status').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const ma_tin_tuc = this.dataset.id;
            const trang_thai_hien_thi = this.checked ? 1 : 0;
            fetch('index.php?c=news&a=updateStatus', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `ma_tin_tuc=${ma_tin_tuc}&trang_thai_hien_thi=${trang_thai_hien_thi}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(trang_thai_hien_thi ? 'Đã bật trạng thái ' : 'Đã tắt trạng thái');
                } else {
                    showToast('Cập nhật thất bại');
                }
            });
        });
    });
});

// Toast thông báo góc phải
function showToast(message) {
    let toast = document.getElementById("toast");
    if (!toast) {
        toast = document.createElement("div");
        toast.id = "toast";
        toast.className = "toast";
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.classList.add("show");
    setTimeout(() => toast.classList.remove("show"), 3000);
}