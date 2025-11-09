<?php
/**
 * Model xử lý đăng nhập người dùng
 * 
 * Tệp này chứa class LoginModel - Model trong mô hình MVC,
 * chịu trách nhiệm xử lý logic nghiệp vụ liên quan đến đăng nhập.
 * 
 * @author Đội phát triển
 * @version 1.0
 */

// Import file cấu hình kết nối Supabase
require_once __DIR__ . '/../config/supabase.php';

/**
 * Class LoginModel - Model xử lý đăng nhập
 * 
 * Class này chứa các phương thức để:
 * - Xác thực thông tin đăng nhập
 * - Kết nối với cơ sở dữ liệu Supabase
 * - Trả về thông tin người dùng khi đăng nhập thành công
 */
class LoginModel {
    
    /**
     * Kiểm tra thông tin đăng nhập của người dùng
     * 
     * Phương thức này thực hiện:
     * 1. Tìm kiếm người dùng trong database theo email
     * 2. So sánh mật khẩu người dùng nhập với mật khẩu trong database
     * 3. Trả về thông tin người dùng nếu đăng nhập thành công
     * 
     * @param string $email Email của người dùng
     * @param string $password Mật khẩu của người dùng
     * 
     * @return array|false Trả về thông tin người dùng nếu đăng nhập thành công,
     *                     false nếu đăng nhập thất bại
     * 
     * @example
     * $loginModel = new LoginModel();
     * $user = $loginModel->checkLogin('admin@gmail.com', '12345678');
     * if ($user) {
     *     echo "Đăng nhập thành công! Xin chào " . $user['ten_nguoi_dung'];
     * } else {
     *     echo "Đăng nhập thất bại!";
     * }
     */
    public function checkLogin($email, $password) {
        // Gửi yêu cầu lấy thông tin user từ Supabase database
        $userResponse = supabase_request("GET", "users", [
            "email" => "eq.$email",    // Điều kiện tìm kiếm: email bằng với $email
            "select" => "*"            // Lấy tất cả các cột
        ]);

        // Debug để xem response - TẮT (chỉ bật khi cần debug)
        // echo "<pre>Supabase Response: "; print_r($userResponse); echo "</pre>";

        // Kiểm tra response thành công: status 200 và có dữ liệu trả về
        if (isset($userResponse['status']) && $userResponse['status'] == 200 && 
            isset($userResponse['data']) && is_array($userResponse['data']) && 
            count($userResponse['data']) > 0) {
            
            // Lấy thông tin user đầu tiên từ kết quả tìm kiếm
            $user = $userResponse['data'][0];
            
            // Kiểm tra mật khẩu có khớp không (plain text)
            if (isset($user['mat_khau']) && $user['mat_khau'] === $password) {
                // Nếu đang dùng mật khẩu mặc định (email) thì đánh dấu bắt đổi mật khẩu
                if (isset($user['email']) && $user['mat_khau'] === $user['email']) {
                    $user['require_password_change'] = true;
                }
                return $user; // Đăng nhập thành công, trả về thông tin user
            }
        } else {
            // Debug lỗi - TẮT (chỉ bật khi cần debug lỗi)
            // echo "<pre>Login failed. Status: " . ($userResponse['status'] ?? 'unknown') . "</pre>";
            // if (isset($userResponse['data']['message'])) {
            //     echo "<pre>Error: " . $userResponse['data']['message'] . "</pre>";
            // }
        }
        
        // Trường hợp đăng nhập thất bại
        // Có thể do: email không tồn tại, mật khẩu sai, hoặc lỗi kết nối database
        return false;
    }
}

/**
 * Cấu trúc dữ liệu users trong database:
 * 
 * Table: users
 * - id (int): ID duy nhất của người dùng
 * - ten_nguoi_dung (string): Tên hiển thị của người dùng
 * - email (string): Email đăng nhập (unique)
 * - mat_khau (string): Mật khẩu (nên mã hóa trong production)
 * - so_dien_thoai (string): Số điện thoại
 * - ngay_sinh (date): Ngày sinh
 * - gioi_tinh (string): Giới tính
 * - dia_chi (string): Địa chỉ
 * - avatar (string): URL ảnh đại diện
 * - ma_role (int): Mã vai trò (1: Admin, 2: Nhân viên, 3: Khách hàng)
 * - created_at (timestamp): Thời gian tạo tài khoản
 * - updated_at (timestamp): Thời gian cập nhật cuối
 */
