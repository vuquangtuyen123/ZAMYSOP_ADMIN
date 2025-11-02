<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require_once __DIR__ . '/../config/supabase.php';
require_once __DIR__ . '/../model/news_model.php';

$model  = new NewsModel();
$method = $_SERVER['REQUEST_METHOD'];

// Lấy các tham số chung
$search = $_GET['search'] ?? '';
$limit  = isset($_GET['limit']) ? (int)$_GET['limit'] : 0;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

// GET – Lấy danh sách hoặc 1 tin cụ thể

if ($method === 'GET') {
    $ma_tin_tuc = $_GET['ma_tin_tuc'] ?? null;

    if ($ma_tin_tuc) {
        $news = $model->getNewsById($ma_tin_tuc);
        echo json_encode(['success' => true, 'data' => $news]);
        exit;
    }

    $data  = $model->getAllNews([], $search, $limit, $offset);
    $total = $model->getTotalNews([], $search);

    echo json_encode([
        'success' => true,
        'total'   => $total,
        'data'    => $data
    ]);
    exit;
}


// POST – Thêm tin mới

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || empty($input['tieu_de']) || empty($input['noi_dung'])) {
        echo json_encode(['success' => false, 'message' => 'Thiếu tiêu đề hoặc nội dung']);
        exit;
    }

    $data = [
        'tieu_de'            => trim($input['tieu_de']),
        'noi_dung'           => trim($input['noi_dung']),
        'hinh_anh'           => $input['hinh_anh'] ?? null,
        'trang_thai_hien_thi' => isset($input['trang_thai_hien_thi'])
            ? (bool)$input['trang_thai_hien_thi']
            : true
    ];

    $result = $model->createNews($data);
    echo json_encode(['success' => $result]);
    exit;
}


// PATCH – Cập nhật tin hoặc trạng thái (sửa bằng mã body json cx đc, mà thêm mã trên url cx đc)

if ($method === 'PATCH') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    // Nhận mã tin từ query, hoặc body với 2 key khả dụng
    $ma_tin_tuc = $_GET['ma_tin_tuc']
        ?? $input['ma_tin_tuc']
        ?? $input['ma_tintuc']
        ?? null;

    if (!$ma_tin_tuc) {
        echo json_encode(['success' => false, 'message' => 'Thiếu ma_tin_tuc']);
        exit;
    }

    // Nếu chỉ cập nhật trạng thái hiển thị
    if (isset($input['trang_thai_hien_thi']) && count($input) === 2) {
        $result = $model->updateStatus($ma_tin_tuc, (bool)$input['trang_thai_hien_thi']);
        echo json_encode(['success' => $result]);
        exit;
    }

    // Cập nhật toàn bộ tin tức
    $data = [
        'ma_tin_tuc'         => $ma_tin_tuc,
        'tieu_de'            => $input['tieu_de'] ?? '',
        'noi_dung'           => $input['noi_dung'] ?? '',
        'hinh_anh'           => $input['hinh_anh'] ?? null,
        'trang_thai_hien_thi' => isset($input['trang_thai_hien_thi'])
            ? (bool)$input['trang_thai_hien_thi']
            : true
    ];

    $result = $model->updateNews($data);
    echo json_encode(['Sửa tin tức thành công' => $result]);
    exit;
}


// DELETE – Xóa tin tức

if ($method === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    // Nhận mã tin từ query hoặc body (2 key khả dụng)
    $ma_tin_tuc = $_GET['ma_tin_tuc']
        ?? $input['ma_tin_tuc']
        ?? $input['ma_tintuc']
        ?? null;

    if (!$ma_tin_tuc) {
        echo json_encode(['success' => false, 'message' => 'Thiếu ma_tin_tuc']);
        exit;
    }

    $result = $model->deleteNews($ma_tin_tuc);
    echo json_encode(['Xóa tin tức thành công' => $result]);
    exit;
}

// Phương thức không hợp lệ

echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
exit;
