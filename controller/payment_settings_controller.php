<?php
require_once __DIR__ . '/../model/payment_settings_model.php';
require_once __DIR__ . '/../config/auth.php';

class Payment_settingsController {

    private $model;

    public function __construct() {
        $this->model = new PaymentSettingsModel();
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function index() {
        require_login();
        require_capability('product.crud'); // Chỉ admin mới có quyền
        
        $paymentMethods = $this->model->getAllPaymentMethods();
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);
        
        require_once __DIR__ . '/../view/payment_settings/index.php';
    }

    public function updateStatus() {
        require_login();
        require_capability('product.crud');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
            exit;
        }

        $code = trim($_POST['code'] ?? '');
        $isActive = filter_var($_POST['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);
        
        if (empty($code)) {
            echo json_encode(['success' => false, 'message' => 'Mã phương thức không hợp lệ']);
            exit;
        }

        $success = $this->model->updatePaymentMethodStatus($code, $isActive);
        
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Cập nhật thành công' : 'Cập nhật thất bại'
        ]);
        exit;
    }

    public function update() {
        require_login();
        require_capability('product.crud');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['message'] = 'Phương thức không hợp lệ';
            header('Location: index.php?c=payment_settings&a=index');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'ten_phuong_thuc' => $_POST['ten_phuong_thuc'] ?? '',
            'mo_ta' => $_POST['mo_ta'] ?? null,
            'thu_tu_hien_thi' => isset($_POST['thu_tu_hien_thi']) ? (int)$_POST['thu_tu_hien_thi'] : 0,
            'icon' => $_POST['icon'] ?? null,
        ];

        if ($id <= 0) {
            $_SESSION['message'] = 'ID không hợp lệ';
            header('Location: index.php?c=payment_settings&a=index');
            exit;
        }

        $success = $this->model->updatePaymentMethod($id, $data);
        $_SESSION['message'] = $success ? 'Cập nhật thành công' : 'Cập nhật thất bại';
        header('Location: index.php?c=payment_settings&a=index');
        exit;
    }
}

?>

