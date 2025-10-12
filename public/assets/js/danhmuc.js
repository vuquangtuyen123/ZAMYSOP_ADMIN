/**
 * JavaScript cho trang quản lý danh mục
 * Chứa các chức năng: Live search, xóa danh mục, animations
 */

// Biến global
let searchTimeout;

/**
 * Khởi tạo khi trang đã load xong
 */
document.addEventListener('DOMContentLoaded', function() {
    const inputTimKiem = document.getElementById('tim-kiem-danh-muc');
    
    // Animation cho các dòng dữ liệu ban đầu
    animateRows();
    
    // Thiết lập live search
    if (inputTimKiem) {
        setupLiveSearch(inputTimKiem);
    }
});

/**
 * Thiết lập tìm kiếm real-time
 */
function setupLiveSearch(inputElement) {
    inputElement.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const tuKhoa = this.value.trim();
        
        // Debounce 300ms để tránh gửi quá nhiều request
        searchTimeout = setTimeout(() => {
            timKiemLive(tuKhoa);
        }, 300);
    });
}

/**
 * Thực hiện tìm kiếm AJAX
 */
function timKiemLive(tuKhoa) {
    const bangDuLieu = document.querySelector('.bang-du-lieu-danh-muc tbody');
    
    // Hiển thị loading
    showLoadingState(bangDuLieu);
    
    // Gửi AJAX request
    fetch('index.php?c=danhmuc&a=search', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ search: tuKhoa })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hienThiKetQua(data.data, tuKhoa);
        } else {
            showErrorState(bangDuLieu, data.message);
        }
    })
    .catch(error => {
        console.error('Lỗi tìm kiếm:', error);
        showErrorState(bangDuLieu, 'Có lỗi xảy ra khi tìm kiếm');
    });
}

/**
 * Hiển thị trạng thái loading
 */
function showLoadingState(bangDuLieu) {
    bangDuLieu.innerHTML = `
        <tr>
            <td colspan="4" style="text-align: center; padding: 40px;">
                <i class="fas fa-spinner fa-spin" style="font-size: 24px; color: #6c757d;"></i>
                <div style="margin-top: 10px; color: #6c757d;">Đang tìm kiếm...</div>
            </td>
        </tr>
    `;
}

/**
 * Hiển thị trạng thái lỗi
 */
function showErrorState(bangDuLieu, message) {
    bangDuLieu.innerHTML = `
        <tr>
            <td colspan="4" style="text-align: center; padding: 40px; color: #dc3545;">
                <i class="fas fa-exclamation-triangle" style="font-size: 24px; margin-bottom: 10px;"></i>
                <div>${message}</div>
            </td>
        </tr>
    `;
}

/**
 * Hiển thị kết quả tìm kiếm
 */
function hienThiKetQua(danhSach, tuKhoa) {
    const bangDuLieu = document.querySelector('.bang-du-lieu-danh-muc tbody');
    
    // Nếu không có kết quả
    if (danhSach.length === 0) {
        bangDuLieu.innerHTML = `
            <tr class="dong-khong-co-du-lieu">
                <td colspan="4" class="thong-bao-khong-co-du-lieu">
                    ${tuKhoa ? 
                        `<i class="fas fa-search"></i> Không có kết quả` 
                        : 
                        `<i class="fas fa-info-circle"></i> Chưa có danh mục nào. Hãy thêm danh mục đầu tiên!`
                    }
                </td>
            </tr>
        `;
        return;
    }
    
    // Tạo HTML cho các dòng kết quả
    let html = '';
    danhSach.forEach(danhMuc => {
        // Highlight từ khóa tìm kiếm
        let tenDanhMuc = danhMuc.ten_danh_muc;
        if (tuKhoa) {
            tenDanhMuc = highlightKeyword(tenDanhMuc, tuKhoa);
        }
        
        html += `
            <tr class="dong-du-lieu-danh-muc">
                <td class="ma-danh-muc">${danhMuc.ma_danh_muc}</td>
                <td class="ten-danh-muc">${tenDanhMuc}</td>
                <td class="ngay-tao">
                    ${formatDate(danhMuc.created_at)}
                </td>
                <td class="cac-nut-thao-tac">
                    <a href="index.php?c=danhmuc&a=edit&id=${danhMuc.ma_danh_muc}" 
                       class="nut-thao-tac sua" title="Sửa danh mục">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button class="nut-thao-tac xoa" title="Xóa danh mục" 
                            onclick="xoaDanhMuc(${danhMuc.ma_danh_muc})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    bangDuLieu.innerHTML = html;
    
    // Animate các dòng mới
    animateRows();
}

/**
 * Highlight từ khóa trong text
 */
function highlightKeyword(text, keyword) {
    const regex = new RegExp(`(${keyword})`, 'gi');
    return text.replace(regex, '<mark style="background: #ffeb3b; padding: 2px 4px; border-radius: 2px;">$1</mark>');
}

/**
 * Format ngày tháng
 */
function formatDate(dateString) {
    return new Date(dateString).toLocaleString('vi-VN');
}

/**
 * Animation cho các dòng dữ liệu
 */
function animateRows() {
    const rows = document.querySelectorAll('.dong-du-lieu-danh-muc');
    rows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateY(10px)';
        setTimeout(() => {
            row.style.opacity = '1';
            row.style.transform = 'translateY(0)';
        }, index * 50);
    });
}

/**
 * Xóa danh mục với xác nhận
 */
function xoaDanhMuc(maDanhMuc) {
    if (confirm('Bạn có chắc chắn muốn xóa danh mục này không?')) {
        window.location.href = 'index.php?c=danhmuc&a=delete&id=' + maDanhMuc;
    }
}