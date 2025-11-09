-- Migration: Thêm cột nhân viên xử lý đơn hàng
-- Lưu ID của nhân viên (admin) xử lý đơn hàng

ALTER TABLE public.orders 
ADD COLUMN IF NOT EXISTS ma_nhan_vien_xu_ly INTEGER REFERENCES public.users(id) ON DELETE SET NULL;

-- Tạo index để tối ưu truy vấn
CREATE INDEX IF NOT EXISTS idx_orders_ma_nhan_vien_xu_ly 
ON public.orders(ma_nhan_vien_xu_ly);

COMMENT ON COLUMN public.orders.ma_nhan_vien_xu_ly IS 'ID nhân viên (admin) xử lý đơn hàng này';

