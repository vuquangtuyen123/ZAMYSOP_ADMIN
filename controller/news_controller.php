<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once __DIR__ . '/../model/news_model.php'; 
require_once __DIR__ . '/../config/supabase.php';

class NewsController {
    private $model;
    private $itemsPerPage = 2; // Số mục trên mỗi trang

    public function __construct() {
        $this->model = new NewsModel();
        session_start(); // Bắt đầu session để lưu thông báo
    }
    public function index() {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

    if (isset($_GET['reset'])) {
        $search = '';
    }

    $offset = ($page - 1) * $this->itemsPerPage;
    $totalNews = $this->model->getTotalNews([], $search);
    $totalPages = ceil($totalNews / $this->itemsPerPage);

    $news = $this->model->getAllNews([], $search, $this->itemsPerPage, $offset);

    // Lấy thông báo từ session
    $message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
    unset($_SESSION['message']);

    require_once __DIR__ . '/../view/news/index.php';
}
   
    public function them() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'tieu_de' => $_POST['tieu_de'],
                'noi_dung' => $_POST['noi_dung'],
                'trang_thai_hien_thi' => isset($_POST['trang_thai_hien_thi']) ? 1 : 0
                
            ];
            if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['hinh_anh'];
                $filePath = $file['tmp_name'];
                $fileName = uniqid() . '-' . preg_replace('/[^a-zA-Z0-9-_\.]/', '', basename($file['name'])); // Loại bỏ ký tự đặc biệt
                $uploadResult = supabase_storage_upload('news-images', $fileName, $filePath);
                if ($uploadResult['error']) {
                    die('Lỗi upload hình ảnh: ' . $uploadResult['message']);
                }
                global $SUPABASE_STORAGE_URL;
                $data['hinh_anh'] = $SUPABASE_STORAGE_URL . '/object/news-images/' . rawurlencode($fileName);
            }
            $this->model->createNews($data);
            $_SESSION['message'] = 'Thêm thành công';
            header('Location: index.php?c=news&a=index');
            exit;
        } else {
            require_once __DIR__ . '/../view/news/them.php';
        }
    }

public function sua($ma_tin_tuc = null) {
    if ($ma_tin_tuc === null) {
        $ma_tin_tuc = isset($_GET['ma_tin_tuc']) ? $_GET['ma_tin_tuc'] : null;
        if ($ma_tin_tuc === null) {
            die('Lỗi: Mã tin tức không được cung cấp.');
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $news = $this->model->getNewsById($ma_tin_tuc);
        $data = [
            'ma_tin_tuc' => $ma_tin_tuc,
            'tieu_de' => $_POST['tieu_de'],
            'noi_dung' => $_POST['noi_dung'],
            'trang_thai_hien_thi' => isset($_POST['trang_thai_hien_thi']) ? 1 : 0
        ];
        if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK) {
            // Xử lý upload ảnh mới
            $file = $_FILES['hinh_anh'];
            $filePath = $file['tmp_name'];
            $fileName = uniqid() . '-' . preg_replace('/[^a-zA-Z0-9-_\.]/', '', basename($file['name']));
            $uploadResult = supabase_storage_upload('news-images', $fileName, $filePath);
            if ($uploadResult['error']) {
                die('Lỗi upload hình ảnh: ' . $uploadResult['message']);
            }
            global $SUPABASE_STORAGE_URL;
            $data['hinh_anh'] = $SUPABASE_STORAGE_URL . '/object/news-images/' . rawurlencode($fileName);
        } else {
            $data['hinh_anh'] = $_POST['hinh_anh_hien_tai']; // Giữ nguyên ảnh cũ nếu không chọn mới
        }
        $this->model->updateNews($data);
        $_SESSION['message'] = 'Cập nhật thành công';
        header('Location: index.php?c=news&a=index');
        exit;
    } else {
        $news = $this->model->getNewsById($ma_tin_tuc);
        require_once __DIR__ . '/../view/news/sua.php';
    }
}
    public function xoa($ma_tin_tuc = null) {
        if ($ma_tin_tuc === null) {
            $ma_tin_tuc = isset($_GET['ma_tin_tuc']) ? $_GET['ma_tin_tuc'] : null;
            if ($ma_tin_tuc === null) {
                die('Lỗi: Mã tin tức không được cung cấp.');
            }
        }

        $news = $this->model->getNewsById($ma_tin_tuc);
        if ($news && !empty($news['hinh_anh'])) {
            global $SUPABASE_STORAGE_URL, $SUPABASE_KEY;
            $filePath = str_replace($SUPABASE_STORAGE_URL . '/object/', '', $news['hinh_anh']);
            $url = $SUPABASE_STORAGE_URL . '/object/news-images/' . rawurlencode($filePath);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer $SUPABASE_KEY"
            ]);

            $response = curl_exec($ch);
            $err = curl_error($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($err || $http_code >= 400) {
                // Bỏ qua lỗi xóa file nếu không thành công
            }
        }
        $this->model->deleteNews($ma_tin_tuc);
         $_SESSION['message'] = 'Xóa thành công';
    header('Location: index.php?c=news&a=index');
    exit;
    }

    public function detail($ma_tin_tuc = null) {
        if ($ma_tin_tuc === null) {
            $ma_tin_tuc = isset($_GET['ma_tin_tuc']) ? $_GET['ma_tin_tuc'] : null;
        }

        if (!isset($ma_tin_tuc) || empty($ma_tin_tuc)) {
            die('Lỗi: Mã tin tức không được cung cấp.');
        }
        $news = $this->model->getNewsById($ma_tin_tuc);
        if (!$news) {
            die('Lỗi: Không tìm thấy tin tức với mã ' . htmlspecialchars($ma_tin_tuc));
        }
        require_once __DIR__ . '/../view/news/detail.php';
    }

   
public function updateNews($data) {
    $updateFields = [];
    if (isset($data['tieu_de'])) $updateFields['tieu_de'] = $data['tieu_de'];
    if (isset($data['noi_dung'])) $updateFields['noi_dung'] = $data['noi_dung'];
    if (isset($data['hinh_anh'])) $updateFields['hinh_anh'] = $data['hinh_anh'];
    if (isset($data['trang_thai_hien_thi'])) $updateFields['trang_thai_hien_thi'] = $data['trang_thai_hien_thi'];
    if (isset($data['tieu_de']) || isset($data['noi_dung']) || isset($data['hinh_anh'])) {
        $updateFields['ngay_dang'] = date('Y-m-d H:i:s');
    }
    $result = supabase_request('PATCH', 'news', ['ma_tin_tuc' => 'eq.' . $data['ma_tin_tuc']], $updateFields);
    return !$result['error'];
}

public function updateStatus() {
    $ma_tin_tuc = $_POST['ma_tin_tuc'] ?? null;
    $trang_thai_hien_thi = isset($_POST['trang_thai_hien_thi']) ? intval($_POST['trang_thai_hien_thi']) : 0;
    if ($ma_tin_tuc) {
        $result = $this->model->updateStatus($ma_tin_tuc, $trang_thai_hien_thi);
        echo json_encode(['success' => $result]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}
}