<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once __DIR__ . '/../model/collection_model.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/supabase.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');

class CollectionController {

    private $model;
    private $itemsPerPage = 8;

    public function __construct() {
        $this->model = new CollectionModel();
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    // Trang danh sách (tất cả người dùng đã login đều xem được)
    public function index() {
        require_login();

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        if (isset($_GET['reset'])) $search = '';

        $total = $this->model->countAll($search);
        $totalPages = max(1, ceil($total / $this->itemsPerPage));
        $collections = $this->model->getAll($search, $this->itemsPerPage, $page);

        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        require_once __DIR__ . '/../view/collection/index.php';
    }

    // Thêm mới (chỉ Admin)
    public function them() {
        require_login();
        require_capability('product.crud');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'ten_bo_suu_tap' => trim($_POST['ten_bo_suu_tap'] ?? ''),
                'mo_ta' => $_POST['mo_ta'] ?? null,
                'trang_thai' => isset($_POST['trang_thai']) ? 1 : 0,
                'images' => []
            ];

            if (empty($data['ten_bo_suu_tap'])) {
                $_SESSION['message'] = 'Vui lòng nhập tên bộ sưu tập';
                header('Location: index.php?c=collection&a=them');
                exit;
            }

            $ma_bo_suu_tap = $this->model->create($data);
            if ($ma_bo_suu_tap) {
                // Upload ảnh
                global $SUPABASE_STORAGE_URL;
                if (!empty($_FILES['images']['name'][0])) {
                    $count = count($_FILES['images']['name']);
                    for ($i = 0; $i < $count; $i++) {
                        if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                            $tmp = $_FILES['images']['tmp_name'][$i];
                            $ext = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
                            $safe = 'img_' . uniqid() . '_' . time() . '.' . $ext;
                            $storagePath = 'collections/bosuutap_' . $ma_bo_suu_tap . '/' . $safe;
                            $up = supabase_storage_upload('product-images', $storagePath, $tmp);
                            if (!$up['error']) {
                                $url = $SUPABASE_STORAGE_URL . '/object/public/product-images/' . rawurlencode($storagePath);
                                $this->model->addImage($ma_bo_suu_tap, $url);
                            }
                        }
                    }
                }
                $_SESSION['message'] = 'Thêm bộ sưu tập thành công';
            } else {
                $_SESSION['message'] = 'Lỗi khi thêm bộ sưu tập';
            }

            header('Location: index.php?c=collection&a=index');
            exit;
        }

        require_once __DIR__ . '/../view/collection/them.php';
    }

    // Sửa (chỉ Admin)
    public function sua($ma_bo_suu_tap = null) {
        require_login();
        require_capability('product.crud');

        if ($ma_bo_suu_tap === null) {
            $ma_bo_suu_tap = $_GET['ma_bo_suu_tap'] ?? null;
            if ($ma_bo_suu_tap === null) die('Lỗi: Mã bộ sưu tập không được cung cấp.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'ten_bo_suu_tap' => trim($_POST['ten_bo_suu_tap'] ?? ''),
                'mo_ta' => $_POST['mo_ta'] ?? null,
                'trang_thai' => isset($_POST['trang_thai']) ? 1 : 0,
                'images' => []
            ];

            // Upload ảnh
            global $SUPABASE_STORAGE_URL;
            if (!empty($_FILES['images']['name'][0])) {
                $count = count($_FILES['images']['name']);
                for ($i = 0; $i < $count; $i++) {
                    if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                        $tmp = $_FILES['images']['tmp_name'][$i];
                        $ext = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
                        $safe = 'img_' . uniqid() . '_' . time() . '.' . $ext;
                        $storagePath = 'collections/bosuutap_' . $ma_bo_suu_tap . '/' . $safe;
                        $up = supabase_storage_upload('product-images', $storagePath, $tmp);
                        if (!$up['error']) {
                            $url = $SUPABASE_STORAGE_URL . '/object/public/product-images/' . rawurlencode($storagePath);
                            $data['images'][] = $url;
                        }
                    }
                }
            }

            $this->model->update($ma_bo_suu_tap, $data);
            $_SESSION['message'] = 'Cập nhật bộ sưu tập thành công';
            header('Location: index.php?c=collection&a=index');
            exit;
        } else {
            $collection = $this->model->getById($ma_bo_suu_tap);
            require_once __DIR__ . '/../view/collection/sua.php';
        }
    }

    // Xóa (chỉ Admin)
    public function xoa($ma_bo_suu_tap = null) {
        require_login();
        require_capability('product.crud');

        if ($ma_bo_suu_tap === null) {
            $ma_bo_suu_tap = $_GET['ma_bo_suu_tap'] ?? null;
            if ($ma_bo_suu_tap === null) die('Lỗi: Mã bộ sưu tập không được cung cấp.');
        }

        $this->model->delete($ma_bo_suu_tap);
        $_SESSION['message'] = 'Xóa bộ sưu tập thành công';
        header('Location: index.php?c=collection&a=index');
        exit;
    }

    // Toggle trạng thái (chỉ Admin)
    public function toggleStatus() {
        require_login();
        require_capability('product.crud');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $ma = (int)($_POST['ma_bo_suu_tap'] ?? 0);
        $value = (int)($_POST['value'] ?? 0);

        if ($ma <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'Mã không hợp lệ']);
            exit;
        }

        $ok = $this->model->update($ma, ['trang_thai' => $value ? 1 : 0]);
        header('Content-Type: application/json');
        echo json_encode(['ok' => (bool)$ok]);
        exit;
    }
}
?>
