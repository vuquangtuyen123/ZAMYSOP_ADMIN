<?php
// api/add_review.php - HOÀN CHỈNH: HỖ TRỢ NHIỀU ẢNH TỪ URL & FILE

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../model/comment_model.php';
require_once '../config/supabase.php';

try {
    error_log("=== ADD REVIEW START ===");

    // 1Lấy dữ liệu từ POST hoặc JSON
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    $productId = $_POST['ma_san_pham'] ?? ($input['ma_san_pham'] ?? null);
    $userId = $_POST['ma_nguoi_dung'] ?? ($input['ma_nguoi_dung'] ?? null);
    $comment = $_POST['noi_dung_danh_gia'] ?? ($input['noi_dung_danh_gia'] ?? null);
    $rating = $_POST['diem_danh_gia'] ?? ($input['diem_danh_gia'] ?? null);
    $parentReviewId = $_POST['ma_danh_gia_cha'] ?? ($input['ma_danh_gia_cha'] ?? null);
    $imageUrls = $input['duong_dan_anh'] ?? null; // Có thể là 1 URL hoặc mảng URL

    if (!$productId || !$userId || !$comment) {
        echo json_encode(['error' => true, 'message' => 'Thiếu thông tin bắt buộc']);
        exit;
    }

    // 2Trạng thái cảm xúc
    $commentModel = new CommentModel();
    $status = $parentReviewId ? 1 : $commentModel->predictSentiment($comment);

    // 3Chuẩn bị dữ liệu review
    $reviewData = [
        'ma_san_pham' => (int)$productId,
        'ma_nguoi_dung' => (int)$userId,
        'noi_dung_danh_gia' => $comment,
        'trang_thai' => $status,
        'thoi_gian_tao' => date('c'),
        'thoi_gian_cap_nhat' => date('c')
    ];
    if ($rating !== null) $reviewData['diem_danh_gia'] = (int)$rating;
    if ($parentReviewId !== null) $reviewData['ma_danh_gia_cha'] = (int)$parentReviewId;

    // 4 Thêm đánh giá vào Supabase
    $response = supabase_request('POST', 'reviews', ['select' => 'ma_danh_gia'], $reviewData);
    if ($response['error']) {
        error_log("Supabase insert error: " . $response['message']);
        echo json_encode(['error' => true, 'message' => 'Thêm đánh giá thất bại']);
        exit;
    }

    $reviewId = $response['data'][0]['ma_danh_gia'] ?? null;
    if (!$reviewId) {
        echo json_encode(['error' => true, 'message' => 'Không lấy được mã đánh giá']);
        exit;
    }

    error_log("✅ Review created: ma_danh_gia = $reviewId");

    // 5 Xử lý upload ảnh
    $uploadedImages = [];

    // --- 5.1: Nếu có file upload ---
    if (isset($_FILES['hinh_anh']) && !empty($_FILES['hinh_anh']['name'])) {
        $files = $_FILES['hinh_anh'];
        $fileCount = is_array($files['name']) ? count($files['name']) : 1;

        for ($i = 0; $i < $fileCount; $i++) {
            $tmp = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
            $name = is_array($files['name']) ? $files['name'][$i] : $files['name'];
            $error = is_array($files['error']) ? $files['error'][$i] : $files['error'];

            if ($error !== UPLOAD_ERR_OK) continue;

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($ext, $allowed)) continue;

            $newFileName = "reviews/review_{$reviewId}_" . time() . "_{$i}.{$ext}";
            $upload = supabase_storage_upload('review-images', $newFileName, $tmp);

            if (!$upload['error'] && isset($upload['data']['path'])) {
                $publicUrl = "https://acddbjalchiruigappqg.supabase.co/storage/v1/object/public/" . $upload['data']['path'];
                $uploadedImages[] = $publicUrl;
                supabase_request('POST', 'review_images', [], [
                    'ma_danh_gia' => $reviewId,
                    'duong_dan_anh' => $publicUrl,
                    'thoi_gian_tao' => date('c'),
                    'thoi_gian_cap_nhat' => date('c')
                ]);
            }
        }
    }

    // --- 5.2: Nếu gửi qua JSON (URL ảnh) ---
    elseif (!empty($imageUrls)) {
        $urls = is_array($imageUrls) ? $imageUrls : [$imageUrls];
        $index = 0;

        foreach ($urls as $imgUrl) {
            error_log("🖼 Đang tải ảnh từ URL: $imgUrl");
            $imageContent = @file_get_contents($imgUrl);

            if ($imageContent === false) {
                error_log("❌ Không tải được ảnh từ URL: $imgUrl");
                continue;
            }

            $ext = pathinfo(parse_url($imgUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (!$ext) $ext = 'jpg';
            $tmpFile = sys_get_temp_dir() . "/tmp_review_" . uniqid() . ".$ext";
            file_put_contents($tmpFile, $imageContent);

            $newFileName = "reviews/review_{$reviewId}_" . time() . "_{$index}.{$ext}";
            $upload = supabase_storage_upload('review-images', $newFileName, $tmpFile);

            if (!$upload['error'] && isset($upload['data']['path'])) {
                $publicUrl = "https://acddbjalchiruigappqg.supabase.co/storage/v1/object/public/" . $upload['data']['path'];
                $uploadedImages[] = $publicUrl;
                supabase_request('POST', 'review_images', [], [
                    'ma_danh_gia' => $reviewId,
                    'duong_dan_anh' => $publicUrl,
                    'thoi_gian_tao' => date('c'),
                    'thoi_gian_cap_nhat' => date('c')
                ]);
            } else {
                error_log("❌ Upload thất bại cho ảnh $imgUrl: " . $upload['message']);
            }

            unlink($tmpFile);
            $index++;
        }
    }

    // 6Trả kết quả
    echo json_encode([
        'error' => false,
        'message' => 'Thêm đánh giá thành công',
        'ma_danh_gia' => $reviewId,
        'anh_da_tai_len' => $uploadedImages
    ]);

    error_log("=== ADD REVIEW END ===");
} catch (Exception $e) {
    error_log("❌ Exception: " . $e->getMessage());
    echo json_encode(['error' => true, 'message' => 'Lỗi hệ thống']);
}
?>
