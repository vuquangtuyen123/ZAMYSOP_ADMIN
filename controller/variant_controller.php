<?php
require_once __DIR__ . '/../model/variant_model.php';

class VariantController {
    private $model;

    public function __construct() {
        $this->model = new VariantModel();
    }

    // =================== DANH SÁCH BIẾN THỂ ===================
    public function index() {
        $keyword = $_GET['keyword'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;

        $result = $this->model->getAll($keyword, $limit, $page);
        $variants = $result['data'];
        $total = $result['total'];
        $totalPages = ceil($total / $limit);

        require_once __DIR__ . '/../view/variant/index.php';
    }

    // =================== THÊM BIẾN THỂ ===================
    public function add() {
        $options = $this->model->getOptions();
        $products = $options['products'];
        $colors = $options['colors'];
        $sizes = $options['sizes'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ma_san_pham = (int)($_POST['ma_san_pham'] ?? 0);
            $ma_mau = (int)($_POST['ma_mau'] ?? 0);
            $ma_size = (int)($_POST['ma_size'] ?? 0);
            $ton_kho = $_POST['ton_kho'] ?? '';

            // Ràng buộc số nguyên dương
            if (!preg_match('/^\d+$/', $ton_kho)) {
                header("Location: index.php?c=variant&a=add&error=" . urlencode("Số lượng tồn kho phải là số nguyên dương."));
                exit;
            }

            $data = [
                'ma_san_pham' => $ma_san_pham,
                'ma_mau' => $ma_mau,
                'ma_size' => $ma_size,
                'ton_kho' => (int)$ton_kho
            ];

            $result = $this->model->insert($data);

            if ($result['status'] === 'update') {
                $msg = "Cập nhật thêm {$result['so_luong_them']} sản phẩm cho {$result['ten_sp']} (size {$result['ten_size']}, màu {$result['ten_mau']}).";
            } else {
                $msg = "Đã thêm biến thể mới cho {$result['ten_sp']} (size {$result['ten_size']}, màu {$result['ten_mau']}).";
            }

            header("Location: index.php?c=variant&a=index&success=" . urlencode($msg));
            exit;
        }

        require_once __DIR__ . '/../view/variant/them.php';
    }

    // =================== SỬA BIẾN THỂ ===================
   public function edit() {
    $id = $_GET['id'] ?? 0;
    $variant = $this->model->getById($id);

    if (!$variant) {
        header("Location: index.php?c=variant&a=index&error=" . urlencode("Không tìm thấy biến thể."));
        exit;
    }

    // Lấy danh sách options để hiển thị
    $options = $this->model->getOptions();
    $products = $options['products'];
    $colors = $options['colors'];
    $sizes = $options['sizes'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ton_kho = $_POST['ton_kho'];

        // Chỉ cho nhập số nguyên dương
        if (!preg_match('/^\d+$/', $ton_kho)) {
            header("Location: index.php?c=variant&a=edit&id=$id&error=" . urlencode("Số lượng tồn kho phải là số nguyên dương."));
            exit;
        }

        $data = [
            'ma_bien_the' => (int)$id,
            'ton_kho' => (int)$ton_kho
        ];

        $success = $this->model->updateQuantity($data);

        if ($success) {
            header("Location: index.php?c=variant&a=index&success=" . urlencode("Cập nhật tồn kho thành công."));
        } else {
            header("Location: index.php?c=variant&a=index&error=" . urlencode("Không thể cập nhật tồn kho."));
        }
        exit;
    }

    require_once __DIR__ . '/../view/variant/sua.php';
}

    // =================== XÓA BIẾN THỂ ===================
    public function delete() {
        $id = $_GET['id'] ?? 0;
        if ($this->model->delete($id)) {
            header("Location: index.php?c=variant&a=index&success=" . urlencode("Xóa biến thể thành công."));
        } else {
            header("Location: index.php?c=variant&a=index&error=" . urlencode("Không thể xóa biến thể."));
        }
        exit;
    }
}
?>
