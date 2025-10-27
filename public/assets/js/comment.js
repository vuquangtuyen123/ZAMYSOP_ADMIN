// Toggle menu điểm đánh giá
function toggleRating() {
    const menu = document.getElementById('rating-menu');
    if (!menu) {
        console.error('Không tìm thấy menu điểm đánh giá!');
        return;
    }
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

// Đóng menu điểm đánh giá khi click ra ngoài
document.addEventListener('click', function(event) {
    const ratingMenu = document.getElementById('rating-menu');
    const ratingButton = document.querySelector('button[onclick="toggleRating()"]');
    if (ratingMenu && ratingButton && !ratingMenu.contains(event.target) && !ratingButton.contains(event.target)) {
        ratingMenu.style.display = 'none';
    }
});

// Chuyển tab giữa "Danh sách đánh giá" và "Sản phẩm đánh giá"
function switchTab(tab) {
    if (!['reviews', 'products'].includes(tab)) {
        console.error('Giá trị tab không hợp lệ:', tab);
        return;
    }
    window.location.href = `index.php?c=comment&a=index&tab=${tab}&page=${currentPage}`;
}

// Toggle hiển thị/ẩn các phản hồi con
function toggleReplies(id) {
    const replies = document.getElementById(`replies-${id}`);
    const button = event.target;
    if (!replies || !button) {
        console.error(`Không tìm thấy container phản hồi hoặc nút cho ID: ${id}`);
        return;
    }
    const count = parseInt(button.getAttribute('data-count')) || 0;
    const isHidden = replies.style.display === 'none' || !replies.style.display;
    replies.style.display = isHidden ? 'block' : 'none';
    button.textContent = isHidden ? `Ẩn phản hồi (${count})` : `Xem phản hồi (${count})`;
}

// Toggle hiển thị/ẩn form phản hồi
function toggleReplyBox(id) {
    const replyBox = document.getElementById(`reply-${id}`);
    if (!replyBox) {
        console.error(`Không tìm thấy hộp phản hồi cho ID: ${id}`);
        return;
    }
    const isHidden = replyBox.style.display === 'none' || !replyBox.style.display;
    replyBox.style.display = isHidden ? 'flex' : 'none';
}

// Toggle chọn/bỏ chọn tất cả checkbox
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.review-checkbox');
    if (!selectAllCheckbox) {
        console.error('Không tìm thấy checkbox chọn tất cả!');
        return;
    }
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
}

// Hiển thị toast notification
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    if (!toast) {
        console.error('Không tìm thấy phần tử toast!');
        return;
    }
    toast.textContent = message || 'Không có thông báo';
    toast.className = `toast ${type}`;
    setTimeout(() => {
        toast.classList.add('show');
    }, 100); // Delay nhỏ để đảm bảo CSS áp dụng
    setTimeout(() => {
        toast.classList.remove('show');
    }, 4000); // Hiển thị trong 4 giây
    setTimeout(() => {
        toast.textContent = '';
        toast.className = 'toast';
    }, 4300); // Reset sau khi ẩn
}

// Xác nhận hành động (ẩn, hiện, xóa, gửi phản hồi)
function confirmAction(message, successMessage) {
    if (confirm(message)) {
        showToast(successMessage, 'success');
        return true;
    }
    return false;
}

// Xác nhận xóa tất cả
function confirmDeleteAll() {
    if (confirm('Bạn có chắc chắn muốn xóa tất cả đánh giá?')) {
        showToast('Đã xóa tất cả đánh giá', 'success');
        return true;
    }
    return false;
}