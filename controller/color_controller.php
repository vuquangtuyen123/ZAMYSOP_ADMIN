<?php
require_once __DIR__ . '/../model/color_model.php';
require_once __DIR__ . '/../config/auth.php';

class ColorController {

    private $model;
    private $itemsPerPage = 8;

    public function __construct() {
        $this->model = new ColorModel();
    }

    // Danh sách màu
    public function index() {
        require_login();
        // Cả Admin và Moderator đều xem được
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $offset = ($page - 1) * $this->itemsPerPage;
        $total = $this->model->getTotalColors();
        $totalPages = max(1, ceil($total / $this->itemsPerPage));
        $colors = $this->model->getAllColors($this->itemsPerPage, $offset);
        require __DIR__ . '/../view/color/index.php';
    }

    // Thêm màu
    public function them() {
        require_login();
        // Admin hoặc Moderator đều thêm được
        if (!can('product.crud') && !can('product.edit')) {
            http_response_code(403); echo 'Bạn không có quyền thực hiện chức năng này.'; exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ten = trim($_POST['ten_mau'] ?? '');
            $hex = trim($_POST['ma_mau_hex'] ?? '');
            if ($ten !== '' && $hex !== '') {
                $this->model->create($ten, $hex);
                header('Location: index.php?c=color&a=index'); exit;
            }
        }
        require __DIR__ . '/../view/color/them.php';
    }

    // Sửa màu
    public function sua() {
        require_login();
        // Admin hoặc Moderator đều sửa được
        if (!can('product.crud') && !can('product.edit')) {
            http_response_code(403); echo 'Bạn không có quyền thực hiện chức năng này.'; exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ten = trim($_POST['ten_mau'] ?? '');
            $hex = trim($_POST['ma_mau_hex'] ?? '');
            if ($id && $ten !== '' && $hex !== '') {
                $this->model->update($id, $ten, $hex);
                header('Location: index.php?c=color&a=index'); exit;
            }
        }

        $colors = $this->model->getAllColors();
        $current = null;
        foreach ($colors as $c) {
            if ((int)$c['ma_mau'] === $id) {
                $current = $c;
                break;
            }
        }
        require __DIR__ . '/../view/color/sua.php';
    }

    // Xóa màu
    public function xoa() {
        require_login();
        // Chỉ Admin mới xóa
        require_capability('product.crud');

        $id = (int)($_GET['id'] ?? 0);
        if ($id) $this->model->delete($id);
        header('Location: index.php?c=color&a=index'); exit;
    }
}
?>
