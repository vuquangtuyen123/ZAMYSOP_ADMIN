<?php
require_once __DIR__ . '/../model/banner_model.php'; 
require_once __DIR__ . '/../config/supabase.php';


class BannerController {
    private $model;

    public function __construct() {
        $this->model = new BannerModel();
    }

    public function index() {
        $banners = $this->model->getAllBanners();
        $message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
        unset($_SESSION['message']);
        require_once __DIR__ . '/../view/banner/index.php';
    }

    
public function them() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['hinh_anh'];
            $filePath = $file['tmp_name'];
            $fileName = uniqid() . '-' . preg_replace('/[^a-zA-Z0-9-_\.]/', '', basename($file['name']));
            $uploadResult = supabase_storage_upload('banner-images', $fileName, $filePath);
            if ($uploadResult['error']) {
                die('Lỗi upload hình ảnh: ' . $uploadResult['message']);
            }
            global $SUPABASE_STORAGE_URL;
            $hinh_anh_url = $SUPABASE_STORAGE_URL . '/object/banner-images/' . rawurlencode($fileName);
        } else {
            $hinh_anh_url = '';
        }
        $data = [
            'hinh_anh' => $hinh_anh_url,
            'trang_thai' => isset($_POST['trang_thai']) ? 1 : 0
        ];
        $this->model->createBanner($data);
        $_SESSION['message'] = 'Thêm banner thành công';
        header('Location: index.php?c=banner&a=index');
        exit;
    } else {
        require_once __DIR__ . '/../view/banner/them.php';
    }
}


public function xoa() {
    $ma_banner = $_GET['ma_banner'] ?? null;
    if ($ma_banner) {
        $this->model->deleteBanner($ma_banner);
        $_SESSION['message'] = 'Xóa banner thành công';
    }
    header('Location: index.php?c=banner&a=index');
    exit;
}
public function updateStatus() {
    $ma_banner = $_POST['ma_banner'] ?? null;
    $trang_thai = isset($_POST['trang_thai']) && $_POST['trang_thai'] == '1' ? true : false;

    if ($ma_banner) {
        $result = $this->model->updateBannerStatus($ma_banner, $trang_thai);
        echo json_encode(['success' => $result]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}
}