<?php
header('Access-Control-Allow-Origin: *'); // Hoặc 'http://localhost:51324' cho bảo mật
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once __DIR__ . '/../config/supabase.php';
require_once __DIR__ . '/../model/banner_model.php';

$model = new BannerModel();

// Lấy phương thức gọi API
$method = $_SERVER['REQUEST_METHOD'];

//  Lấy tham số (nếu có)
$ma_banner = $_GET['ma_banner'] ?? null;

// API GET – Lấy danh sách banner

if ($method === 'GET') {
    $data = $model->getAllBanners();
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

//  API POST – Thêm banner mới

if ($method === 'POST') {
    // Đọc JSON body
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['hinh_anh'])) {
        echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu']);
        exit;
    }

    $data = [
        'hinh_anh' => $input['hinh_anh'],
        'trang_thai' => isset($input['trang_thai']) ? (bool)$input['trang_thai'] : 0
    ];
    $result = $model->createBanner($data);
    echo json_encode(['Thêm mới banner thành công' => $result]);
    exit;
}

// API PATCH – Cập nhật trạng thái banner

if ($method === 'PATCH') {
    $raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?? [];
$ma_banner = $_GET['ma_banner'] ?? $input['ma_banner'] ?? null;
    if (!$ma_banner) {
        echo json_encode(['success' => false, 'message' => 'Thiếu ma_banner']);
        exit;
    }
    $trang_thai = isset($input['trang_thai']) ? (bool)$input['trang_thai'] : false;
    $result = $model->updateBannerStatus($ma_banner, $trang_thai);
    echo json_encode(['Cập nhật banner thành công' => $result]);
    exit;
}

//  API DELETE – Xóa banner
if ($method === 'DELETE') {
    // Đọc dữ liệu đầu vào (nếu có gửi JSON body)
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?? [];

    // Lấy ma_banner từ query hoặc body
    $ma_banner = $_GET['ma_banner'] ?? $input['ma_banner'] ?? null;

    if (!$ma_banner) {
        echo json_encode(['success' => false, 'message' => 'Thiếu ma_banner']);
        exit;
    }

    $result = $model->deleteBanner($ma_banner);
    echo json_encode([
        'success' => (bool)$result,
        'message' => $result ? 'Xóa banner thành công' : 'Không thể xóa banner'
    ]);
    exit;
}



//  Mặc định: không hỗ trợ phương thức khác

echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
exit;
