<?php
/**
 * Controller xử lý quản lý danh mục sản phẩm
 * 
 * Tệp này chứa class CategoryController - Controller trong mô hình MVC,
 * chịu trách nhiệm điều khiển luồng xử lý danh mục sản phẩm.
 * 
 * @author Đội phát triển
 * @version 1.0
 */

// Import model xử lý danh mục
require_once __DIR__ . '/../../model/danhmuc_model.php';

/**
 * Class DanhmucController - Controller xử lý danh mục
 * 
 * Class này chứa các phương thức để:
 * - Hiển thị danh sách danh mục
 * - Thêm danh mục mới
 * - Sửa danh mục
 * - Xóa danh mục
 */
class DanhmucController {
    
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
     * Hiển thị trang danh sách danh mục
     * 
     * Method này sẽ:
     * 1. Kiểm tra xác thực người dùng
     * 2. Lấy danh sách danh mục từ Supabase
     * 3. Include view danh mục
     */
    private $itemsPerPage = 8;

    public function index() {
        // Kiểm tra đăng nhập (tương tự dashboard)
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?c=login&a=login');
            exit();
        }
        
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $offset = ($page - 1) * $this->itemsPerPage;
        $total = $this->categoryModel->getTotalDanhMuc();
        $totalPages = max(1, ceil($total / $this->itemsPerPage));
        
        // Lấy danh sách danh mục từ Supabase với pagination
        $ketQuaDanhMuc = $this->categoryModel->layTatCaDanhMuc($this->itemsPerPage, $offset);
        
        // Truyền dữ liệu cho view
        $danhSachDanhMuc = $ketQuaDanhMuc['data'];
        $thongBaoLoi = !$ketQuaDanhMuc['success'] ? $ketQuaDanhMuc['message'] : '';
        
        // Kiểm tra thông báo từ session
        $thongBaoThanhCong = '';
        if (isset($_SESSION['thong_bao_thanh_cong'])) {
            $thongBaoThanhCong = $_SESSION['thong_bao_thanh_cong'];
            unset($_SESSION['thong_bao_thanh_cong']); // Xóa sau khi hiển thị
        }
        
        // Kiểm tra thông báo lỗi từ session
        if (isset($_SESSION['thong_bao_loi'])) {
            $thongBaoLoi = $_SESSION['thong_bao_loi'];
            unset($_SESSION['thong_bao_loi']); // Xóa sau khi hiển thị
        }
        
        // Include view danh mục
        include __DIR__ . '/../../view/danhmuc/danhmuc.php';
    }
    
    /**
     * Hiển thị form thêm danh mục mới
     */
    public function create() {
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
    
    /**
     * Hiển thị form sửa danh mục
     */
    public function edit() {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?c=login&a=login');
            exit();
        }
        
        // Lấy ID danh mục từ GET parameter
        $maDanhMuc = $_GET['id'] ?? null;
        
        // Validate ID
        if (empty($maDanhMuc) || !is_numeric($maDanhMuc)) {
            $_SESSION['thong_bao_loi'] = 'ID danh mục không hợp lệ!';
            header('Location: index.php?c=danhmuc&a=index');
            exit();
        }
        
        // Lấy thông tin danh mục
        $ketQuaDanhMuc = $this->categoryModel->layDanhMucTheoId($maDanhMuc);
        
        // Khởi tạo biến thông báo
        $thongBaoLoi = '';
        $thongBaoThanhCong = '';
        
        if (!$ketQuaDanhMuc['success']) {
            $thongBaoLoi = $ketQuaDanhMuc['message'];
            $danhMuc = null;
        } else {
            $danhMuc = $ketQuaDanhMuc['data'];
        }
        
        // Include view sửa danh mục
        include __DIR__ . '/../../view/danhmuc/suadanhmuc.php';
    }
    
    /**
     * Xử lý cập nhật danh mục
     */
    public function update() {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?c=login&a=login');
            exit();
        }
        
        // Lấy ID danh mục từ GET parameter
        $maDanhMuc = $_GET['id'] ?? null;
        
        // Validate ID
        if (empty($maDanhMuc) || !is_numeric($maDanhMuc)) {
            $_SESSION['thong_bao_loi'] = 'ID danh mục không hợp lệ!';
            header('Location: index.php?c=danhmuc&a=index');
            exit();
        }
        
        // Lấy thông tin danh mục hiện tại
        $ketQuaDanhMucHienTai = $this->categoryModel->layDanhMucTheoId($maDanhMuc);
        if (!$ketQuaDanhMucHienTai['success']) {
            $_SESSION['thong_bao_loi'] = 'Không tìm thấy danh mục cần sửa!';
            header('Location: index.php?c=danhmuc&a=index');
            exit();
        }
        $danhMuc = $ketQuaDanhMucHienTai['data'];
        
        // Khởi tạo biến thông báo
        $thongBaoLoi = '';
        $thongBaoThanhCong = '';
        
        // Kiểm tra method POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $thongBaoLoi = 'Phương thức không hợp lệ!';
            include __DIR__ . '/../../view/danhmuc/suadanhmuc.php';
            return;
        }
        
        // Lấy và validate dữ liệu
        $tenDanhMucMoi = trim($_POST['ten_danh_muc'] ?? '');
        
        // Validation
        if (empty($tenDanhMucMoi)) {
            $thongBaoLoi = 'Vui lòng nhập tên danh mục!';
        } elseif (strlen($tenDanhMucMoi) < 2) {
            $thongBaoLoi = 'Tên danh mục phải có ít nhất 2 ký tự!';
        } elseif (strlen($tenDanhMucMoi) > 100) {
            $thongBaoLoi = 'Tên danh mục không được vượt quá 100 ký tự!';
        } elseif ($tenDanhMucMoi === $danhMuc['ten_danh_muc']) {
            $thongBaoLoi = 'Tên danh mục mới phải khác với tên hiện tại!';
        }
        
        // Nếu có lỗi, hiển thị lại form
        if (!empty($thongBaoLoi)) {
            include __DIR__ . '/../../view/danhmuc/suadanhmuc.php';
            return;
        }
        
        // Thực hiện cập nhật danh mục
        $ketQua = $this->categoryModel->capNhatDanhMuc($maDanhMuc, $tenDanhMucMoi);
        
        if ($ketQua['success']) {
            // Thành công - redirect về trang danh sách với thông báo
            $_SESSION['thong_bao_thanh_cong'] = 'Cập nhật danh mục "' . $tenDanhMucMoi . '" thành công!';
            header('Location: index.php?c=danhmuc&a=index');
            exit();
        } else {
            // Thất bại - hiển thị lỗi
            $thongBaoLoi = $ketQua['message'];
            include __DIR__ . '/../../view/danhmuc/suadanhmuc.php';
        }
    }
    
    /**
     * Xử lý xóa danh mục
     */
    public function delete() {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?c=login&a=login');
            exit();
        }
        
        // Lấy ID danh mục từ GET parameter
        $maDanhMuc = $_GET['id'] ?? null;
        
        // Validate ID
        if (empty($maDanhMuc) || !is_numeric($maDanhMuc)) {
            $_SESSION['thong_bao_loi'] = 'ID danh mục không hợp lệ!';
            header('Location: index.php?c=danhmuc&a=index');
            exit();
        }
        
        // Kiểm tra danh mục có tồn tại không
        $danhMucHienTai = $this->categoryModel->layDanhMucTheoId($maDanhMuc);
        if (!$danhMucHienTai['success']) {
            $_SESSION['thong_bao_loi'] = 'Không tìm thấy danh mục cần xóa!';
            header('Location: index.php?c=danhmuc&a=index');
            exit();
        }
        
        // Thực hiện xóa danh mục
        $ketQua = $this->categoryModel->xoaDanhMuc($maDanhMuc);
        
        if ($ketQua['success']) {
            // Thành công
            $_SESSION['thong_bao_thanh_cong'] = 'Xóa danh mục "' . $danhMucHienTai['data']['ten_danh_muc'] . '" thành công!';
        } else {
            // Thất bại
            $_SESSION['thong_bao_loi'] = $ketQua['message'];
        }
        
        // Redirect về trang danh sách
        header('Location: index.php?c=danhmuc&a=index');
        exit();
    }
    
    /**
     * API tìm kiếm danh mục cho AJAX
     */
    public function search() {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }
        
        // Chỉ chấp nhận POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit();
        }
        
        // Lấy từ khóa tìm kiếm
        $input = json_decode(file_get_contents('php://input'), true);
        $tuKhoaTimKiem = trim($input['search'] ?? '');
        
        // Thực hiện tìm kiếm
        if (!empty($tuKhoaTimKiem)) {
            $ketQua = $this->categoryModel->timKiemDanhMuc($tuKhoaTimKiem);
        } else {
            $ketQua = $this->categoryModel->layTatCaDanhMuc();
        }
        
        // Trả về JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $ketQua['success'],
            'message' => $ketQua['message'],
            'data' => $ketQua['data'],
            'search_term' => $tuKhoaTimKiem
        ]);
        exit();
    }
    
    
}
?>