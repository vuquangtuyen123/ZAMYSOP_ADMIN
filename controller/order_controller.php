<?php
require_once __DIR__ . '/../model/order_model.php';
require_once __DIR__ . '/../config/auth.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');

class OrderController {
    private $model;

    public function __construct() {
        $this->model = new OrderModel();
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function index() {
        require_login();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 8;
        $code = trim($_GET['code'] ?? '');
        $customer = trim($_GET['customer'] ?? '');
        $status = $_GET['status'] ?? '';

        $ordersRes = $this->model->searchOrders($code, $customer, $status, $page, $limit);
        $total = $this->model->countSearchOrders($code, $customer, $status);
        $statusesRes = $this->model->getStatuses();

        $orders = $ordersRes['error'] ? [] : ($ordersRes['data'] ?? []);
        // Đảm bảo orders là array và không rỗng
        if (!is_array($orders)) {
            $orders = [];
        }
        
        // DEBUG: Hiển thị dữ liệu raw ra màn hình
        if (isset($_GET['debug'])) {
            echo '<div style="background:#f0f0f0; padding:20px; margin:20px; border:2px solid red;">';
            echo '<h2>DEBUG INFO</h2>';
            echo '<h3>ordersRes:</h3>';
            echo '<pre>' . print_r($ordersRes, true) . '</pre>';
            echo '<h3>orders (first 2 items):</h3>';
            echo '<pre>' . print_r(array_slice($orders, 0, 2), true) . '</pre>';
            echo '<h3>orders count:</h3>';
            echo '<p>' . count($orders) . '</p>';
            echo '<h3>orders type:</h3>';
            echo '<p>' . gettype($orders) . '</p>';
            if (!empty($orders[0])) {
                echo '<h3>First order keys:</h3>';
                echo '<pre>' . print_r(array_keys($orders[0]), true) . '</pre>';
                echo '<h3>First order full:</h3>';
                echo '<pre>' . print_r($orders[0], true) . '</pre>';
            }
            echo '</div>';
        }
        
        $totalPages = $total > 0 ? ceil($total / $limit) : 1;
        $statuses = $statusesRes['error'] ? [] : ($statusesRes['data'] ?? []);
        if (!is_array($statuses)) {
            $statuses = [];
        }

        require_once __DIR__ . '/../view/order/index.php';
    }

    public function processing() {
        require_login();
        $statusId = 1;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 8;
        $code = trim($_GET['code'] ?? '');
        $customer = trim($_GET['customer'] ?? '');

        $ordersRes = $this->model->searchByStatus($statusId, $code, $customer, $page, $limit);
        $total = $this->model->countByStatusWithSearch($statusId, $code, $customer);
        $statusesRes = $this->model->getStatuses();

        $orders = $ordersRes['error'] ? [] : ($ordersRes['data'] ?? []);
        $totalPages = $total > 0 ? ceil($total / $limit) : 1;
        $statuses = $statusesRes['error'] ? [] : ($statusesRes['data'] ?? []);

        require_once __DIR__ . '/../view/order/processing.php';
    }

    public function completed() {
        require_login();
        $statusId = 4;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 8;
        $code = trim($_GET['code'] ?? '');
        $customer = trim($_GET['customer'] ?? '');

        $ordersRes = $this->model->searchByStatus($statusId, $code, $customer, $page, $limit);
        $total = $this->model->countByStatusWithSearch($statusId, $code, $customer);
        $statusesRes = $this->model->getStatuses();

        $orders = $ordersRes['error'] ? [] : ($ordersRes['data'] ?? []);
        $totalPages = $total > 0 ? ceil($total / $limit) : 1;
        $statuses = $statusesRes['error'] ? [] : ($statusesRes['data'] ?? []);

        require_once __DIR__ . '/../view/order/completed.php';
    }

    public function getDetail() {
        require_login();
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['error' => 'ID không hợp lệ']);
            exit;
        }

        $orderRes = $this->model->getByIdFull($id);
        $itemsRes = $this->model->getOrderDetails($id);

        if ($orderRes['error'] || !$orderRes['data']) {
            echo json_encode(['error' => 'Đơn hàng không tồn tại']);
            exit;
        }

        echo json_encode([
            'order' => $orderRes['data'],
            'items' => $itemsRes['error'] ? [] : $itemsRes['data']
        ]);
        exit;
    }

    public function updateAction() {
        require_login();
        
        // Đảm bảo không có output trước JSON
        if (ob_get_level() > 0) {
            ob_clean();
        }
        header('Content-Type: application/json; charset=UTF-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';
        $lyDoHuy = trim($_POST['ly_do_huy'] ?? '');

        // Validation: nếu là cancel thì phải có lý do
        if ($action === 'cancel' && empty($lyDoHuy)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập lý do hủy đơn']);
            exit;
        }

        if ($id <= 0 || !in_array($action, ['confirm', 'deliver', 'complete', 'return', 'cancel'])) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            exit;
        }

        // Map hành động sang trạng thái
        $statusMap = [
            'confirm'  => 2, // Đã xác nhận
            'deliver'  => 3, // Đang giao hàng
            'complete' => 4, // Hoàn tất
            'return'   => 6, // Đã trả hàng
            'cancel'   => 5  // Đã hủy
        ];

        $newStatus = $statusMap[$action];
        $autoDelivery = ($action === 'complete');

        // Lấy ID nhân viên từ session
        $staffId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

        try {
            // Cập nhật trạng thái trong model
            $result = $this->model->updateStatus($id, $newStatus, $autoDelivery, $lyDoHuy, $staffId);

            // Ghi log thông tin trạng thái hủy/trả hàng
            if (in_array($newStatus, [5, 6]) && !$result['error']) {
                error_log("Đơn hàng #$id đã cập nhật trạng thái $newStatus (Cộng lại tồn kho thành công)");
            }

            echo json_encode([
                'success' => !$result['error'],
                'message' => $result['error'] ? ($result['message'] ?? 'Cập nhật thất bại') : 'Cập nhật thành công'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ]);
        }
        exit;
    }
}
?>
