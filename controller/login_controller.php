<?php
/**
 * Controller xử lý đăng nhập và xác thực người dùng
 * 
 * Tệp này chứa class LoginController - Controller trong mô hình MVC,
 * chịu trách nhiệm điều khiển luồng xử lý đăng nhập, dashboard và đăng xuất.
 * 
 * @author Đội phát triển
 * @version 1.0
 */

// Import model xử lý đăng nhập
require_once __DIR__ . '/../model/login_model.php';
require_once __DIR__ . '/../model/user_model.php';

/**
 * Class LoginController - Controller xử lý xác thực
 * 
 * Class này chứa các phương thức để:
 * - Xử lý form đăng nhập
 * - Hiển thị trang dashboard
 * - Xử lý đăng xuất
 * - Quản lý session người dùng
 */
class LoginController {
    
    /**
     * @var LoginModel Instance của LoginModel để xử lý logic nghiệp vụ đăng nhập
     */
    private $loginModel;
    private $userModel;

    /**
     * Constructor - Khởi tạo controller
     * 
     * Tạo instance của LoginModel để sử dụng trong các phương thức của controller
     */
    public function __construct() {
        $this->loginModel = new LoginModel();
        $this->userModel  = new UserModel();
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    /**
     * Xử lý đăng nhập người dùng
     * 
     * Phương thức này thực hiện:
     * 1. Ngăn cache trang để đảm bảo bảo mật
     * 2. Xử lý dữ liệu form đăng nhập (nếu có POST request)
     * 3. Xác thực thông tin đăng nhập qua LoginModel
     * 4. Tạo session cho người dùng nếu đăng nhập thành công
     * 5. Chuyển hướng đến dashboard hoặc hiển thị lỗi
     * 6. Hiển thị form đăng nhập
     * 
     * @return void
     */
    public function login() {
        // Ngăn cache trang login để tăng cường bảo mật
        // Đảm bảo trang đăng nhập luôn được tải từ server, không từ cache
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        
        $error = ''; // Biến lưu thông báo lỗi
        
        // Kiểm tra xem có dữ liệu đăng nhập được gửi không (POST request)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Lấy dữ liệu từ form, sử dụng ?? '' để tránh lỗi undefined index
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // Gọi model để kiểm tra thông tin đăng nhập
            $user = $this->loginModel->checkLogin($email, $password);

            // Nếu đăng nhập thành công
            if ($user) {
                // Nếu cần bắt đổi mật khẩu (đang dùng mật khẩu mặc định = email)
                if (!empty($user['require_password_change'])) {
                    $_SESSION['pending_change_user_id'] = $user['id'];
                    $_SESSION['pending_change_email'] = $user['email'];
                    header('Location: index.php?c=login&a=changePassword');
                    exit;
                }

                // Lưu thông tin người dùng vào session
                $_SESSION['user_id'] = $user['id'];                    // ID người dùng
                $_SESSION['user_name'] = $user['ten_nguoi_dung'];      // Tên hiển thị
                $_SESSION['user_email'] = $user['email'];              // Email
                // Lưu thông tin vai trò để hệ thống phân quyền (RBAC)
                $_SESSION['role_id'] = isset($user['ma_role']) ? (int)$user['ma_role'] : null;
                $_SESSION['role_name'] = isset($user['ma_role'])
                    ? ($user['ma_role'] == 1 ? 'Administrator' : ($user['ma_role'] == 2 ? 'Moderator' : 'User'))
                    : null;

                header("Location:index.php?c=dashboard&a=index");
                exit;
            } else {
                // Đăng nhập thất bại, lưu thông báo lỗi
                $error = "Sai email hoặc mật khẩu!";
            }
        }
        
        // Hiển thị trang đăng nhập (có thể kèm thông báo lỗi)
        include __DIR__ . '/../view/login.php';
    }

    /**
     * Bắt buộc đổi mật khẩu khi đang dùng mật khẩu mặc định (email)
     */
    public function changePassword() {
        $error = '';
        $email = $_SESSION['pending_change_email'] ?? '';
        $userId = $_SESSION['pending_change_user_id'] ?? null;

        if (!$userId) {
            header('Location: index.php?c=login&a=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            if ($new === '' || $confirm === '' || $new !== $confirm) {
                $error = 'Mật khẩu không hợp lệ hoặc không khớp.';
            } else {
                $ok = $this->userModel->update($userId, ['mat_khau' => $new]);
                if ($ok) {
                    // Xóa trạng thái pending và yêu cầu đăng nhập lại
                    unset($_SESSION['pending_change_user_id'], $_SESSION['pending_change_email']);
                    header('Location: index.php?c=login&a=login');
                    exit;
                } else {
                    $error = 'Không thể cập nhật mật khẩu.';
                }
            }
        }

        include __DIR__ . '/../view/change_password.php';
    }

    /**
     * Hiển thị trang dashboard sau khi đăng nhập thành công
     * 
     * Phương thức này thực hiện:
     * 1. Ngăn cache trang dashboard
     * 2. Kiểm tra người dùng đã đăng nhập chưa
     * 3. Chuyển hướng về trang đăng nhập nếu chưa đăng nhập
     * 4. Hiển thị trang dashboard nếu đã đăng nhập
     * 
     * @return void
     */
    public function dashboard() {
        // Ngăn cache trang dashboard để đảm bảo dữ liệu luôn mới
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        
        // Kiểm tra người dùng đã đăng nhập chưa
        if (!isset($_SESSION['user_id'])) {
            // Chưa đăng nhập, chuyển hướng về trang đăng nhập
            header("Location: index.php?c=login&a=login");
            exit;
        }
        
        // Đã đăng nhập, hiển thị trang dashboard
        include __DIR__ . '/../view/dashboard.php';
    }

    /**
     * Xử lý đăng xuất người dùng
     * 
     * Phương thức này thực hiện:
     * 1. Xóa tất cả dữ liệu session
     * 2. Xóa session cookie trên browser
     * 3. Hủy session hiện tại
     * 4. Tạo session mới để tránh session fixation
     * 5. Chuyển hướng về trang đăng nhập
     * 
     * @return void
     */
    public function logout() {
        // Bước 1: Xóa tất cả session variables
        $_SESSION = array();
        
        // Bước 2: Xóa session cookie trên browser nếu có
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params(); // Lấy thông số cookie hiện tại
            setcookie(session_name(), '', time() - 42000, // Đặt thời gian hết hạn trong quá khứ
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Bước 3: Hủy session hiện tại
        session_destroy();
        
        // Bước 4: Tạo session mới để tránh lỗi bảo mật session fixation
        session_start();
        
        // Bước 5: Chuyển hướng về trang đăng nhập
        header("Location: index.php?c=login&a=login");
        exit; // Dừng thực thi để đảm bảo chuyển hướng
    }
}

/**
 * Ghi chú về bảo mật:
 * 
 * 1. Cache Control:
 *    - Ngăn cache các trang nhạy cảm (login, dashboard)
 *    - Đảm bảo dữ liệu luôn được tải từ server
 * 
 * 2. Session Management:
 *    - Sử dụng session để lưu trạng thái đăng nhập
 *    - Kiểm tra session trước khi truy cập trang bảo mật
 *    - Xóa hoàn toàn session khi đăng xuất
 * 
 * 3. Input Validation:
 *    - Sử dụng ?? '' để tránh lỗi undefined index
 *    - Validation sẽ được thực hiện ở model layer
 * 
 * 4. URL Structure:
 *    - ?c=login&a=login: Controller = login, Action = login
 *    - ?c=login&a=dashboard: Controller = login, Action = dashboard
 *    - Cấu trúc này giúp routing rõ ràng và dễ bảo trì
 */
