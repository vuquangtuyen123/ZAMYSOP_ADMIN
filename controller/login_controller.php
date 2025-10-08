<?php
require_once __DIR__ . '/../model/login_model.php';

class LoginController {
    private $loginModel;

    public function __construct() {
        $this->loginModel = new LoginModel();
    }

    public function login() {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $this->loginModel->checkLogin($email, $password);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                header("Location: index.php?c=login&a=dashboard");
                exit;
            } else {
                $error = "Sai email hoặc mật khẩu!";
            }
        }
        include __DIR__ . '/../view/login.php';
    }

    public function dashboard() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?c=login&a=login");
            exit;
        }
        echo "<h1>Xin chào: " . htmlspecialchars($_SESSION['user_email']) . "</h1>";
        echo "<a href='index.php?c=login&a=logout'>Đăng xuất</a>";
    }

    public function logout() {
        session_destroy();
        header("Location: index.php?c=login&a=login");
        exit;
    }
}
