<?php
/**
 * Entry Point - Điểm vào chính của ứng dụng Zamy Shop Admin
 * 
 * Tệp này là Front Controller trong mô hình MVC, chịu trách nhiệm:
 * - Khởi tạo session
 * - Routing các request đến controller và action tương ứng
 * - Xử lý lỗi khi không tìm thấy controller hoặc action
 * - Tự động load controller theo pattern naming convention
 * 
 * URL Structure: index.php?c=[controller]&a=[action]
 * Ví dụ: index.php?c=login&a=dashboard
 * 
 * @author Đội phát triển
 * @version 1.0
 */

// Khởi tạo session để quản lý trạng thái đăng nhập
session_start();

/**
 * Routing System - Hệ thống định tuyến
 * 
 * Lấy tham số từ URL để xác định controller và action cần thực thi
 * Sử dụng null coalescing operator (??) để set giá trị mặc định
 */
$c = $_GET['c'] ?? 'login';   // Tên controller mặc định là 'login'
$a = $_GET['a'] ?? 'login';   // Tên action mặc định là 'login'

/**
 * Controller Loading System
 * 
 * Tự động tạo tên class controller theo convention:
 * - URL: ?c=login -> Class: LoginController
 * - URL: ?c=user -> Class: UserController
 * - URL: ?c=danhmuc -> Class: DanhmucController
 */
$controllerName = ucfirst($c) . 'Controller';

// Tạo đường dẫn đến file controller
// Kiểm tra nếu có thư mục con cho controller
$controllerFile = __DIR__ . '/../controller/' . $c . '_controller/' . $c . '_controller.php';

// Nếu không tìm thấy trong thư mục con, thử tìm trong thư mục gốc
if (!file_exists($controllerFile)) {
    $controllerFile = __DIR__ . '/../controller/' . $c . '_controller.php';
}

/**
 * Controller Execution Flow
 * 
 * 1. Kiểm tra file controller có tồn tại không
 * 2. Include file controller
 * 3. Kiểm tra class controller có tồn tại không
 * 4. Tạo instance của controller
 * 5. Kiểm tra method (action) có tồn tại không
 * 6. Thực thi method
 */
if (file_exists($controllerFile)) {
    // Include file controller
    require_once $controllerFile;

    // Kiểm tra class controller có tồn tại không
    if (class_exists($controllerName)) {
        // Tạo instance của controller
        $controller = new $controllerName();

        // Kiểm tra action (method) có tồn tại trong controller không
        if (method_exists($controller, $a)) {
            // Thực thi action
            $controller->$a();
        } else {
            // Lỗi: Action không tồn tại
            http_response_code(404);
            echo "<h1>Lỗi 404</h1>";
            echo "<p>Không tìm thấy action: <strong>$a</strong> trong controller <strong>$controllerName</strong></p>";
            echo "<p>Vui lòng kiểm tra lại URL hoặc liên hệ quản trị viên.</p>";
        }
    } else {
        // Lỗi: Class controller không tồn tại
        http_response_code(500);
        echo "<h1>Lỗi hệ thống</h1>";
        echo "<p>Không tìm thấy class: <strong>$controllerName</strong></p>";
        echo "<p>Vui lòng kiểm tra file controller có đúng tên class không.</p>";
    }
} else {
    // Lỗi: File controller không tồn tại
    http_response_code(404);
    echo "<h1>Lỗi 404</h1>";
    echo "<p>Không tìm thấy controller file: <strong>$controllerFile</strong></p>";
    echo "<p>Vui lòng kiểm tra lại URL hoặc tạo controller tương ứng.</p>";
}

/**
 * Ghi chú về cấu trúc MVC và Routing:
 * 
 * 1. MVC Pattern:
 *    - Model: Xử lý logic nghiệp vụ và database (model/)
 *    - View: Hiển thị giao diện người dùng (view/)
 *    - Controller: Điều khiển luồng xử lý (controller/)
 * 
 * 2. File Naming Convention:
 *    - Controller file: [name]_controller.php
 *    - Controller class: [Name]Controller
 *    - Ví dụ: login_controller.php -> LoginController
 * 
 * 3. URL Routing Examples:
 *    - /index.php -> LoginController->login() (mặc định)
 *    - /index.php?c=login&a=dashboard -> LoginController->dashboard()
 *    - /index.php?c=user&a=index -> UserController->index()
 *    - /index.php?c=product&a=create -> ProductController->create()
 * 
 * 4. Security Considerations:
 *    - Input validation sẽ được thực hiện ở controller level
 *    - Session management để kiểm tra quyền truy cập
 *    - Error handling để tránh information disclosure
 * 
 * 5. Future Enhancements:
 *    - Middleware system cho authentication/authorization
 *    - REST API routing cho AJAX requests
 *    - URL rewriting để tạo friendly URLs
 *    - Caching system cho performance optimization
 */
