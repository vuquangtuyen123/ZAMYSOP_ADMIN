<?php
require_once __DIR__ . '/../model/order_model.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');

class OrderController {
    private $model;

    public function __construct() {
        $this->model = new OrderModel();
    }

    public function index() {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 10;
        $code = trim($_GET['code'] ?? '');
        $customer = trim($_GET['customer'] ?? '');
        $status = $_GET['status'] ?? '';

        $ordersRes = $this->model->searchOrders($code, $customer, $status, $page, $limit);
        $total = $this->model->countSearchOrders($code, $customer, $status);
        $statusesRes = $this->model->getStatuses();

        $orders = $ordersRes['error'] ? [] : $ordersRes['data'];
        $totalPages = $total > 0 ? ceil($total / $limit) : 1;
        $statuses = $statusesRes['error'] ? [] : $statusesRes['data'];

        require_once __DIR__ . '/../view/order/index.php';
    }

    public function processing() {
        $statusId = 1;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 10;
        $code = trim($_GET['code'] ?? '');
        $customer = trim($_GET['customer'] ?? '');

        $ordersRes = $this->model->searchByStatus($statusId, $code, $customer, $page, $limit);
        $total = $this->model->countByStatusWithSearch($statusId, $code, $customer);
        $statusesRes = $this->model->getStatuses();

        $orders = $ordersRes['error'] ? [] : $ordersRes['data'];
        $totalPages = $total > 0 ? ceil($total / $limit) : 1;
        $statuses = $statusesRes['error'] ? [] : $statusesRes['data'];

        require_once __DIR__ . '/../view/order/processing.php';
    }

    public function completed() {
        $statusId = 4;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 10;
        $code = trim($_GET['code'] ?? '');
        $customer = trim($_GET['customer'] ?? '');

        $ordersRes = $this->model->searchByStatus($statusId, $code, $customer, $page, $limit);
        $total = $this->model->countByStatusWithSearch($statusId, $code, $customer);
        $statusesRes = $this->model->getStatuses();

        $orders = $ordersRes['error'] ? [] : $ordersRes['data'];
        $totalPages = $total > 0 ? ceil($total / $limit) : 1;
        $statuses = $statusesRes['error'] ? [] : $statusesRes['data'];

        require_once __DIR__ . '/../view/order/completed.php';
    }

    public function getDetail() {
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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';

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

        // Cập nhật trạng thái trong model
        $result = $this->model->updateStatus($id, $newStatus, $autoDelivery);

        // ✅ Ghi log thông tin trạng thái hủy/trả hàng
        if (in_array($newStatus, [5, 6]) && !$result['error']) {
            error_log("Đơn hàng #$id đã cập nhật trạng thái $newStatus (Cộng lại tồn kho thành công)");
        }

        echo json_encode([
            'success' => !$result['error'],
            'message' => $result['error']
                ? 'Cập nhật thất bại'
                : 'Cập nhật thành công'
        ]);
        exit;
    }
}
?>
