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

// Toggle trạng thái banner
window.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toggle-banner-status').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const ma_banner = this.dataset.id;
            const trang_thai = this.checked ? 1 : 0;
            fetch('index.php?c=banner&a=updateStatus', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `ma_banner=${ma_banner}&trang_thai=${trang_thai}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(trang_thai ? 'Banner đã bật ' : 'Banner đã tắt ');
                } else {
                    showToast('Cập nhật trạng thái thất bại');
                }
            });
        });
    });
});