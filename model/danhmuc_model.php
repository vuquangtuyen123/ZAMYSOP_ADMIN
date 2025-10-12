<?php
/**
 * Model xử lý dữ liệu danh mục sản phẩm
 * 
 * Tệp này chứa class CategoryModel - Model trong mô hình MVC,
 * chịu trách nhiệm tương tác với cơ sở dữ liệu Supabase cho bảng categories.
 * 
 * @author Đội phát triển
 * @version 1.0
 */

// Import file cấu hình Supabase
require_once __DIR__ . '/../config/supabase.php';

/**
 * Class CategoryModel - Model xử lý danh mục
 * 
 * Class này chứa các phương thức để:
 * - Lấy danh sách danh mục từ Supabase
 * - Thêm danh mục mới
 * - Cập nhật danh mục
 * - Xóa danh mục
 */
class CategoryModel {
    
    /**
     * Lấy tất cả danh mục từ Supabase
     * 
     * @return array Mảng chứa danh sách danh mục hoặc thông báo lỗi
     */
    public function layTatCaDanhMuc() {
        try {
            // Gọi API Supabase để lấy tất cả danh mục
            $ketQua = supabase_request('GET', 'categories', [
                'select' => 'ma_danh_muc,ten_danh_muc,created_at',
                'order' => 'created_at.desc'
            ]);
            
            if ($ketQua['error']) {
                return [
                    'success' => false,
                    'message' => 'Lỗi khi lấy danh sách danh mục: ' . $ketQua['message'],
                    'data' => []
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Lấy danh sách danh mục thành công',
                'data' => $ketQua['data']
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Lấy thông tin một danh mục theo ID
     * 
     * @param int $maDanhMuc Mã danh mục cần lấy
     * @return array Thông tin danh mục hoặc thông báo lỗi
     */
    public function layDanhMucTheoId($maDanhMuc) {
        try {
            $ketQua = supabase_request('GET', 'categories', [
                'ma_danh_muc' => 'eq.' . $maDanhMuc,
                'select' => 'ma_danh_muc,ten_danh_muc,created_at'
            ]);
            
            if ($ketQua['error'] || empty($ketQua['data'])) {
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy danh mục',
                    'data' => null
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Lấy thông tin danh mục thành công',
                'data' => $ketQua['data'][0]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * Tìm kiếm danh mục theo tên
     * 
     * @param string $tuKhoa Từ khóa tìm kiếm
     * @return array Mảng chứa danh sách danh mục tìm được hoặc thông báo lỗi
     */
    public function timKiemDanhMuc($tuKhoa) {
        try {
            // Nếu từ khóa rỗng, trả về tất cả danh mục
            if (empty(trim($tuKhoa))) {
                return $this->layTatCaDanhMuc();
            }
            
            // Tìm kiếm với Supabase sử dụng operator ilike (case insensitive)
            $ketQua = supabase_request('GET', 'categories', [
                'select' => 'ma_danh_muc,ten_danh_muc,created_at',
                'ten_danh_muc' => 'ilike.*' . $tuKhoa . '*',
                'order' => 'created_at.desc'
            ]);
            
            if ($ketQua['error']) {
                return [
                    'success' => false,
                    'message' => 'Lỗi khi tìm kiếm danh mục: ' . $ketQua['message'],
                    'data' => []
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Tìm kiếm thành công. Tìm thấy ' . count($ketQua['data']) . ' kết quả.',
                'data' => $ketQua['data']
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Thêm danh mục mới
     * 
     * @param string $tenDanhMuc Tên danh mục
     * @return array Kết quả thêm danh mục
     */
    public function themDanhMuc($tenDanhMuc) {
        try {
            $duLieu = [
                'ten_danh_muc' => $tenDanhMuc,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $ketQua = supabase_request('POST', 'categories', [], $duLieu);
            
            if ($ketQua['error']) {
                return [
                    'success' => false,
                    'message' => 'Lỗi khi thêm danh mục: ' . $ketQua['message']
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Thêm danh mục thành công'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Cập nhật thông tin danh mục
     * 
     * @param int $maDanhMuc Mã danh mục cần cập nhật
     * @param string $tenDanhMuc Tên danh mục mới
     * @return array Kết quả cập nhật
     */
    public function capNhatDanhMuc($maDanhMuc, $tenDanhMuc) {
        try {
            $duLieu = [
                'ten_danh_muc' => $tenDanhMuc
            ];
            
            $ketQua = supabase_request('PATCH', 'categories', [
                'ma_danh_muc' => 'eq.' . $maDanhMuc
            ], $duLieu);
            
            if ($ketQua['error']) {
                return [
                    'success' => false,
                    'message' => 'Lỗi khi cập nhật danh mục: ' . $ketQua['message']
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Cập nhật danh mục thành công'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Xóa danh mục
     * 
     * @param int $maDanhMuc Mã danh mục cần xóa
     * @return array Kết quả xóa
     */
    public function xoaDanhMuc($maDanhMuc) {
        try {
            $ketQua = supabase_request('DELETE', 'categories', [
                'ma_danh_muc' => 'eq.' . $maDanhMuc
            ]);
            
            if ($ketQua['error']) {
                return [
                    'success' => false,
                    'message' => 'Lỗi khi xóa danh mục: ' . $ketQua['message']
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Xóa danh mục thành công'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ];
        }
    }
}
?>