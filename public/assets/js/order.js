// assets/js/order.js - ĐÃ SỬA HOÀN CHỈNH
const SUPABASE_URL = 'https://acddbjalchiruigappqg.supabase.co';

document.addEventListener('DOMContentLoaded', function () {
    const popup = document.getElementById('popup-detail');
    const closeBtn = document.querySelector('.close-popup');
    const body = document.getElementById('popup-body');

    // === TOAST THÔNG BÁO ===
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 2500);
    }

    // === XEM CHI TIẾT ĐƠN HÀNG ===
    document.querySelectorAll('.nut-xem').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            fetch(`index.php?c=order&a=getDetail&id=${id}`)
                .then(r => r.json())
                .then(data => {
                    if (data.error || !data.order) {
                        body.innerHTML = `<p style="color:red">Đơn hàng không tồn tại hoặc lỗi tải dữ liệu.</p>`;
                        popup.style.display = 'flex';
                        return;
                    }

                    const o = data.order;
                    const items = data.items;

                    let html = `
                        <div class="chi-tiet-don-hang">
                            <h3>Chi tiết đơn hàng #${o.ma_don_hang}</h3>
                            <div class="thong-tin">
                                <div><strong>Khách hàng:</strong> ${o.users?.ten_nguoi_dung || 'Khách lẻ'}</div>
                                <div><strong>SĐT:</strong> ${o.users?.so_dien_thoai || '—'}</div>
                                <div><strong>Ngày đặt:</strong> ${new Date(o.ngay_dat_hang).toLocaleString('vi-VN')}</div>
                                <div><strong>Ngày giao:</strong> ${o.ngay_giao_hang 
                                    ? '<span style="color:green">' + new Date(o.ngay_giao_hang).toLocaleString('vi-VN') + '</span>' 
                                    : '<em style="color:#999">Chưa giao</em>'}
                                </div>
                                <div><strong>Địa chỉ:</strong> ${o.dia_chi_giao_hang || '—'}</div>
                                <div><strong>Phương thức TT:</strong> ${o.hinh_thuc_thanh_toan == 'vnpay' ? 'VNPay' : 'Tiền mặt'}</div>
                                <div><strong>Trạng thái TT:</strong> 
                                    <span style="color:${o.trang_thai_thanh_toan == 'da_thanh_toan' ? 'green' : 'red'}; font-weight:600;">
                                        ${o.trang_thai_thanh_toan == 'da_thanh_toan' ? 'Đã thanh toán' : 'Chưa thanh toán'}
                                    </span>
                                </div>
                                <div><strong>Ghi chú:</strong> ${o.ghi_chu 
                                    ? '<span style="color:#d35400;">' + o.ghi_chu + '</span>' 
                                    : '—'}
                                </div>
                                <div><strong>Nhân viên xử lý:</strong> ${o.ma_nhan_vien_xu_ly 
                                    ? '<span style="color:#3498db; font-weight:600;">ID: ' + o.ma_nhan_vien_xu_ly + '</span>' 
                                    : '<em style="color:#999;">Chưa xử lý</em>'}
                                </div>
                                ${o.ly_do_huy_hoan_hang 
                                    ? `<div><strong>Lý do hủy đơn:</strong> <span style="color:#e74c3c; font-weight:600; background:#ffe6e6; padding:4px 8px; border-radius:4px; display:inline-block;">${o.ly_do_huy_hoan_hang}</span></div>` 
                                    : ''}
                            </div>

                            <h4>Danh sách sản phẩm</h4>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>SL</th>
                                        <th>Đơn giá</th>
                                        <th>Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>`;

                    items.forEach(item => {
                        const tenSP = item.ten_san_pham || 'Sản phẩm không xác định';
                        const size = item.ten_size || '—';
                        const mau = item.ten_mau || '—';
                        const soLuong = item.so_luong_mua || 0;
                        const donGia = soLuong > 0 ? Math.round(item.thanh_tien / soLuong) : 0;
                        const thanhTien = item.thanh_tien || 0;
                        const hinhAnh = item.duong_dan_anh
                            ? item.duong_dan_anh.split('?')[0]
                            : 'https://via.placeholder.com/60?text=No+Image';

                        html += `
                            <tr>
                                <td class="product-cell">
                                    <img src="${hinhAnh}" alt="${tenSP}" 
                                         onerror="this.src='https://via.placeholder.com/60?text=No+Image'">
                                    <div>
                                        <div class="ten-sp">${tenSP}</div>
                                        <div class="thuoc-tinh">
                                            <span>Size:</span> <strong>${size}</strong> | 
                                            <span>Màu:</span> <strong>${mau}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">${soLuong}</td>
                                <td class="text-right">${donGia.toLocaleString('vi-VN')}đ</td>
                                <td class="text-right text-red">${thanhTien.toLocaleString('vi-VN')}đ</td>
                            </tr>`;
                    });

                    html += `
                                <tr class="tong-cong">
                                    <td colspan="3">TỔNG CỘNG:</td>
                                    <td>${o.tong_gia_tri_don_hang.toLocaleString('vi-VN')}đ</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>`;

                    body.innerHTML = html;
                    popup.style.display = 'flex';
                })
                .catch(err => {
                    console.error(err);
                    body.innerHTML = `<p style="color:red">Lỗi kết nối.</p>`;
                    popup.style.display = 'flex';
                });
        });
    });

    // === ĐÓNG POPUP ===
    if (closeBtn) {
        closeBtn.addEventListener('click', () => popup.style.display = 'none');
    }
    popup.addEventListener('click', e => {
        if (e.target === popup) popup.style.display = 'none';
    });

    // === POPUP LÝ DO HỦY ĐƠN ===
    const cancelPopup = document.getElementById('popup-cancel');
    const cancelForm = document.getElementById('form-huy-don');
    const cancelOrderIdInput = document.getElementById('cancel-order-id');
    const lyDoHuyTextarea = document.getElementById('ly-do-huy');
    const closeCancelBtn = document.querySelector('.close-popup-cancel');
    const btnCancelCancel = document.querySelector('.btn-cancel-cancel');

    function showCancelPopup(orderId) {
        cancelOrderIdInput.value = orderId;
        lyDoHuyTextarea.value = '';
        cancelPopup.style.display = 'flex';
        lyDoHuyTextarea.focus();
    }

    function hideCancelPopup() {
        cancelPopup.style.display = 'none';
        cancelOrderIdInput.value = '';
        lyDoHuyTextarea.value = '';
    }

    // Xử lý form hủy đơn
    if (cancelForm) {
        cancelForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const orderId = cancelOrderIdInput.value;
            const lyDo = lyDoHuyTextarea.value.trim();

            if (!lyDo) {
                showToast('⚠️ Vui lòng nhập lý do hủy đơn!', 'error');
                return;
            }

            // Gửi request hủy đơn với lý do
            fetch('index.php?c=order&a=updateAction', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${orderId}&action=cancel&ly_do_huy=${encodeURIComponent(lyDo)}`
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    hideCancelPopup();
                    showToast('Đơn hàng đã bị hủy!', 'error');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('⚠️ ' + (res.message || 'Không thể hủy đơn hàng!'), 'error');
                }
            })
            .catch(() => {
                showToast('Lỗi kết nối server!', 'error');
            });
        });
    }

    // Đóng popup khi click nút đóng hoặc nút hủy
    if (closeCancelBtn) {
        closeCancelBtn.addEventListener('click', hideCancelPopup);
    }
    if (btnCancelCancel) {
        btnCancelCancel.addEventListener('click', hideCancelPopup);
    }
    if (cancelPopup) {
        cancelPopup.addEventListener('click', e => {
            if (e.target === cancelPopup) hideCancelPopup();
        });
    }

    // === CHỈ MỘT ĐOẠN DUY NHẤT cho hành động ===
    document.querySelectorAll('[data-action]').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const action = this.dataset.action;
            const text = this.textContent.trim();

            // Nếu là hành động hủy, hiển thị popup thay vì confirm
            if (action === 'cancel') {
                showCancelPopup(id);
                return;
            }

            // Các hành động khác vẫn dùng confirm
            let confirmMsg = `Xác nhận "${text}" cho đơn hàng #${id}?`;
            if (!confirm(confirmMsg)) return;

            fetch('index.php?c=order&a=updateAction', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}&action=${action}`
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    // Hiển thị thông báo tương ứng
                    switch (action) {
                        case 'confirm': showToast('Đơn hàng đã được xác nhận!'); break;
                        case 'deliver': showToast('Đơn hàng đang giao!'); break;
                        case 'complete': showToast('Đơn hàng đã hoàn tất!'); break;
                        case 'return': showToast('Đơn hàng đã hoàn hàng!'); break;
                        default: showToast('✔️ Cập nhật trạng thái thành công!');
                    }
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('⚠️ ' + (res.message || 'Không thể cập nhật đơn hàng!'), 'error');
                }
            })
            .catch(() => showToast('Lỗi kết nối server!', 'error'));
        });
    });
});
