// assets/js/variant.js

let popupTimer = null;

// ====== MỞ POPUP XÓA ======
function openPopup(id) {
    const popup = document.getElementById('popupXoa');
    popup.style.display = 'flex';
    popup.dataset.id = id;

    // Nếu popup đang mở thì reset timer cũ
    if (popupTimer) clearTimeout(popupTimer);

    // Đặt timer tự ẩn sau 5 giây
    popupTimer = setTimeout(() => {
        closePopup();
    }, 5000);
}

// ====== ĐÓNG POPUP ======
function closePopup() {
    const popup = document.getElementById('popupXoa');
    popup.style.display = 'none';
    popup.dataset.id = '';
    if (popupTimer) clearTimeout(popupTimer);
}

// ====== XỬ LÝ NHẤN NÚT XÓA TRONG POPUP ======
document.addEventListener('DOMContentLoaded', () => {
    const btnXacNhan = document.getElementById('btnXacNhanXoa');
    if (btnXacNhan) {
        btnXacNhan.addEventListener('click', () => {
            const popup = document.getElementById('popupXoa');
            const id = popup.dataset.id;
            if (id) {
                closePopup();
                window.location.href = `index.php?c=variant&a=delete&id=${id}`;
            }
        });
    }

    // Đóng popup khi click ra ngoài vùng
    window.addEventListener('click', (e) => {
        const popup = document.getElementById('popupXoa');
        if (e.target === popup) closePopup();
    });
});

// ====== TOAST THÔNG BÁO ======
function showToast(message, type = "success") {
    const toast = document.createElement("div");
    toast.id = "toast";
    toast.className = `show ${type}`;
    toast.innerHTML = `<i class="fa ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
