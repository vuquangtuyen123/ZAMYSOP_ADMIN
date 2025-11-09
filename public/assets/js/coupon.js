// Coupon module specific scripts
document.addEventListener('DOMContentLoaded', () => {
    const toggles = document.querySelectorAll('.toggle-status');
    toggles.forEach((el) => {
        el.addEventListener('change', async (e) => {
            const id = el.getAttribute('data-id');
            const value = el.checked ? 1 : 0;
            try {
                const res = await fetch('index.php?c=coupon&a=toggleStatus', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `ma_giam_gia=${encodeURIComponent(id)}&value=${value}`
                });
                const data = await res.json();
                if (!data.ok) {
                    el.checked = !el.checked; // revert on failure
                    alert('Không thể cập nhật trạng thái.');
                }
            } catch (err) {
                el.checked = !el.checked;
                alert('Lỗi kết nối.');
            }
        });
    });

    // Validation cho form thêm/sửa mã giảm giá
    const couponForm = document.getElementById('couponForm');
    if (couponForm) {
        const ngayBatDau = document.getElementById('ngay_bat_dau');
        const ngayKetThuc = document.getElementById('ngay_ket_thuc');
        const ngayBatDauError = document.getElementById('ngay_bat_dau_error');
        const ngayKetThucError = document.getElementById('ngay_ket_thuc_error');

        function validateDates() {
            let isValid = true;
            
            // Xóa thông báo lỗi cũ
            if (ngayBatDauError) {
                ngayBatDauError.style.display = 'none';
                ngayBatDauError.textContent = '';
            }
            if (ngayKetThucError) {
                ngayKetThucError.style.display = 'none';
                ngayKetThucError.textContent = '';
            }

            if (ngayBatDau && ngayKetThuc && ngayBatDau.value && ngayKetThuc.value) {
                const startDate = new Date(ngayBatDau.value);
                const endDate = new Date(ngayKetThuc.value);
                
                if (startDate > endDate) {
                    isValid = false;
                    if (ngayKetThucError) {
                        ngayKetThucError.textContent = 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu';
                        ngayKetThucError.style.display = 'block';
                    }
                    if (ngayBatDau) {
                        ngayBatDau.style.borderColor = '#dc3545';
                    }
                    if (ngayKetThuc) {
                        ngayKetThuc.style.borderColor = '#dc3545';
                    }
                } else {
                    if (ngayBatDau) {
                        ngayBatDau.style.borderColor = '';
                    }
                    if (ngayKetThuc) {
                        ngayKetThuc.style.borderColor = '';
                    }
                }
            }

            return isValid;
        }

        if (ngayBatDau && ngayKetThuc) {
            ngayBatDau.addEventListener('change', validateDates);
            ngayKetThuc.addEventListener('change', validateDates);
        }

        couponForm.addEventListener('submit', (e) => {
            if (!validateDates()) {
                e.preventDefault();
                alert('Vui lòng kiểm tra lại ngày bắt đầu và ngày kết thúc. Ngày bắt đầu không được lớn hơn ngày kết thúc.');
                return false;
            }
        });
    }
});

