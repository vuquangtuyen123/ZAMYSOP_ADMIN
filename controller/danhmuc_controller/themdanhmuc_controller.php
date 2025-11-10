<?php
/**
 * Controller xử lý thêm danh mục sản phẩm
 * 
 * Tệp này chứa class ThemdanhmucController - Controller trong mô hình MVC,
 * chịu trách nhiệm điều khiển luồng thêm danh mục sản phẩm.
 * 
 * @author Đội phát triển
 * @version 1.0
 */

// Import model xử lý danh mục
require_once __DIR__ . '/../../model/danhmuc_model.php';
date_default_timezone_set('Asia/Ho_Chi_Minh'); 
/**
 * Class ThemdanhmucController - Controller xử lý thêm danh mục
 * 
 * Class này chứa các phương thức để:
 * - Hiển thị form thêm danh mục
 * - Xử lý lưu danh mục mới
 */
class ThemdanhmucController {
    
    /**
     * @var CategoryModel Instance của CategoryModel để xử lý logic nghiệp vụ danh mục
     */
    private $categoryModel;
    
    /**
     * Constructor - Khởi tạo CategoryModel
     */
    public function __construct() {
        $this->categoryModel = new CategoryModel();
    }
    
    /**
     * Hiển thị form thêm danh mục mới
     * 
     * Method này sẽ:
     * 1. Kiểm tra xác thực người dùng
     * 2. Include view thêm danh mục
     */
    public function index() {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?c=login&a=login');
            exit();
        }
        
        // Khởi tạo biến thông báo
        $thongBaoLoi = '';
        $thongBaoThanhCong = '';
        
        // Include view thêm danh mục
        include __DIR__ . '/../../view/danhmuc/themdanhmuc.php';
    }
    
    /**
     * Xử lý lưu danh mục mới
     * 
     * Method này sẽ:
     * 1. Kiểm tra xác thực người dùng
     * 2. Validate dữ liệu từ form
     * 3. Lưu danh mục vào database
     * 4. Redirect hoặc hiển thị thông báo
     */
    public function store() {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?c=login&a=login');
            exit();
        }
        
        // Khởi tạo biến thông báo
        $thongBaoLoi = '';
        $thongBaoThanhCong = '';
        
        // Kiểm tra method POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $thongBaoLoi = 'Phương thức không hợp lệ!';
            include __DIR__ . '/../../view/danhmuc/themdanhmuc.php';
            return;
        }
        
        // Lấy và validate dữ liệu
        $tenDanhMuc = trim($_POST['ten_danh_muc'] ?? '');
        
        // Validation
        if (empty($tenDanhMuc)) {
            $thongBaoLoi = 'Vui lòng nhập tên danh mục!';
        } elseif (strlen($tenDanhMuc) < 2) {
            $thongBaoLoi = 'Tên danh mục phải có ít nhất 2 ký tự!';
        } elseif (strlen($tenDanhMuc) > 100) {
            $thongBaoLoi = 'Tên danh mục không được vượt quá 100 ký tự!';
        }
        
        // Nếu có lỗi, hiển thị lại form
        if (!empty($thongBaoLoi)) {
            include __DIR__ . '/../../view/danhmuc/themdanhmuc.php';
            return;
        }
        
        // Thực hiện thêm danh mục
        $ketQua = $this->categoryModel->themDanhMuc($tenDanhMuc);
        
        if ($ketQua['success']) {
            // Thành công - redirect về trang danh sách với thông báo
            $_SESSION['thong_bao_thanh_cong'] = $ketQua['message'];
            header('Location: index.php?c=danhmuc&a=index');
            exit();
        } else {
            // Thất bại - hiển thị lỗi
            $thongBaoLoi = $ketQua['message'];
            include __DIR__ . '/../../view/danhmuc/themdanhmuc.php';
        }
    }
}
?>
