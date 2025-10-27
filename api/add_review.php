<?php
// Thêm header CORS
header('Access-Control-Allow-Origin: *'); // Hoặc 'http://localhost:51324' cho bảo mật
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Xử lý yêu cầu OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../model/comment_model.php';
require_once '../config/supabase.php'; // File cấu hình Supabase

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['ma_san_pham']) || !isset($input['ma_nguoi_dung']) || !isset($input['noi_dung_danh_gia'])) {
    error_log("Missing required fields: " . print_r($input, true));
    echo json_encode(['error' => true, 'message' => 'Thiếu thông tin bắt buộc']);
    exit;
}

$productId = $input['ma_san_pham'];
$userId = $input['ma_nguoi_dung'];
$comment = $input['noi_dung_danh_gia'];
$rating = isset($input['diem_danh_gia']) ? $input['diem_danh_gia'] : null;
$parentReviewId = isset($input['ma_danh_gia_cha']) ? $input['ma_danh_gia_cha'] : null;

try {
    $commentModel = new CommentModel();
    $status = $parentReviewId ? 1 : $commentModel->predictSentiment($comment);
    $reviewData = [
        'ma_san_pham' => $productId,
        'ma_nguoi_dung' => $userId,
        'noi_dung_danh_gia' => $comment,
        'trang_thai' => $status,
        'thoi_gian_tao' => date('c'), // Thêm thời gian tạo
    ];
    if ($parentReviewId == null && $rating != null) {
        $reviewData['diem_danh_gia'] = $rating;
    }
    if ($parentReviewId != null) {
        $reviewData['ma_danh_gia_cha'] = $parentReviewId;
    }

    error_log("Preparing to insert review: " . print_r($reviewData, true));
    // Gửi yêu cầu POST đến Supabase
    $response = supabase_request('POST', 'reviews', ['select' => '*'], $reviewData);
    error_log("Supabase insert response: " . print_r($response, true));

    if ($response['error']) {
        error_log("Supabase insert error: " . $response['message']);
        echo json_encode(['error' => true, 'message' => 'Thêm không thành công: ' . $response['message']]);
        exit;
    }

    // Không kiểm tra ma_danh_gia, chỉ xác nhận thêm thành công
    echo json_encode([
        'error' => false,
        'message' => 'Thêm thành công'
    ]);
} catch (Exception $e) {
    error_log("Error in add_review.php: " . $e->getMessage());
    echo json_encode(['error' => true, 'message' => 'Thêm không thành công: ' . $e->getMessage()]);
}
?>