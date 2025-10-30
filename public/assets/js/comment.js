function toggleRating() {
    const menu = document.getElementById('rating-menu');
    if (!menu) return;
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

document.addEventListener('click', function(e) {
    const menu = document.getElementById('rating-menu');
    const btn = document.querySelector('button[onclick="toggleRating()"]');
    if (menu && btn && !menu.contains(e.target) && !btn.contains(e.target)) {
        menu.style.display = 'none';
    }
});

function switchTab(tab) {
    if (!['reviews', 'products'].includes(tab)) return;
    window.location.href = `index.php?c=comment&a=index&tab=${tab}&page=${currentPage}`;
}

function toggleReplies(id) {
    const replies = document.getElementById(`replies-${id}`);
    const button = event.target;
    if (!replies || !button) return;
    const count = parseInt(button.dataset.count) || 0;
    const isHidden = replies.style.display === 'none' || !replies.style.display;
    replies.style.display = isHidden ? 'block' : 'none';
    button.textContent = isHidden ? `Ẩn phản hồi (${count})` : `Xem phản hồi (${count})`;
}

function toggleReplyBox(id) {
    const box = document.getElementById(`reply-${id}`);
    if (!box) return;
    const isHidden = box.style.display === 'none' || !box.style.display;
    box.style.display = isHidden ? 'flex' : 'none';
}

function toggleSelectAll() {
    const all = document.getElementById('select-all');
    document.querySelectorAll('.review-checkbox').forEach(cb => cb.checked = all.checked);
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.textContent = message;
    toast.className = `toast ${type}`;
    setTimeout(() => toast.classList.add('show'), 100);
    setTimeout(() => toast.classList.remove('show'), 4000);
    setTimeout(() => { toast.textContent = ''; toast.className = 'toast'; }, 4300);
}

function confirmAction(message, successMsg) {
    if (confirm(message)) {
        showToast(successMsg, 'success');
        return true;
    }
    return false;
}

function confirmDeleteAll() {
    if (confirm('Xóa tất cả đánh giá?')) {
        showToast('Đã xóa tất cả', 'success');
        return true;
    }
    return false;
}