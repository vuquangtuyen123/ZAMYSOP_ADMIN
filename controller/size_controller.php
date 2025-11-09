<?php
require_once __DIR__ . '/../model/size_model.php';
require_once __DIR__ . '/../config/auth.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');
class SizeController {

    private $model;
    private $itemsPerPage = 8;

    public function __construct() {
        $this->model = new SizeModel();
    }

    // Danh sách size
    public function index() {
        require_login();
        // Cả Admin và Moderator đều xem được
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $offset = ($page - 1) * $this->itemsPerPage;
        $total = $this->model->getTotalSizes();
        $totalPages = max(1, ceil($total / $this->itemsPerPage));
        $sizes = $this->model->getAllSizes($this->itemsPerPage, $offset);
        require __DIR__ . '/../view/size/index.php';
    }

    // Thêm size
    public function them() {
        require_login();
        // Admin hoặc Moderator đều thêm được
        if (!can('product.crud') && !can('product.edit')) {
            http_response_code(403); echo 'Bạn không có quyền thực hiện chức năng này.'; exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ten = trim($_POST['ten_size'] ?? '');
            if ($ten !== '') {
                $this->model->create($ten);
                header('Location: index.php?c=size&a=index'); exit;
            }
        }
        require __DIR__ . '/../view/size/them.php';
    }

    // Sửa size
    public function sua() {
        require_login();
        // Admin hoặc Moderator đều sửa được
        if (!can('product.crud') && !can('product.edit')) {
            http_response_code(403); echo 'Bạn không có quyền thực hiện chức năng này.'; exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ten = trim($_POST['ten_size'] ?? '');
            if ($id && $ten !== '') {
                $this->model->update($id, $ten);
                header('Location: index.php?c=size&a=index'); exit;
            }
        }

        $sizes = $this->model->getAllSizes();
        $current = null;
        foreach ($sizes as $s) {
            if ((int)$s['ma_size'] === $id) {
                $current = $s;
                break;
            }
        }
        require __DIR__ . '/../view/size/sua.php';
    }

    // Xóa size
    public function xoa() {
        require_login();
        // Chỉ Admin mới xóa
        require_capability('product.crud');

        $id = (int)($_GET['id'] ?? 0);
        if ($id) $this->model->delete($id);
        header('Location: index.php?c=size&a=index'); exit;
    }
}
?>
