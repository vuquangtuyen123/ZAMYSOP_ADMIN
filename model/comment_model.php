<?php
require_once __DIR__ . '/../config/supabase.php';

class CommentModel {
    /* ---------------------- PREDICT SENTIMENT ---------------------- */
    // Dự đoán cảm xúc (tích cực/tiêu cực) của nội dung bình luận bằng script Python
    public function predictSentiment($text) {
        // Thiết lập ghi log lỗi vào stderr để debug
        ini_set('error_log', 'php://stderr');

        // Kiểm tra nếu nội dung rỗng, trả về 1 (tích cực) làm mặc định
        // Điều này đảm bảo không gọi script Python với input rỗng
        if (empty(trim($text))) {
            error_log("predictSentiment: Empty text input -> return default 1");
            return 1;
        }

        // Đường dẫn đến script Python dự đoán cảm xúc
        $scriptPath = realpath(__DIR__ . '/../Model_ML/predict.py');
        // Danh sách lệnh Python để hỗ trợ cả Windows và Linux/Unix
        $pythonCommands = [
            (stripos(PHP_OS, 'WIN') === 0) ? 'python' : '/usr/bin/python3',
            (stripos(PHP_OS, 'WIN') === 0) ? 'python3' : '/usr/bin/python'
        ];

        // Kiểm tra sự tồn tại của file Python, nếu không tồn tại trả về 1
        // Điều này ngăn lỗi khi file Python bị thiếu
        if (!file_exists($scriptPath)) {
            error_log("predictSentiment: Python script not found at $scriptPath");
            return 1;
        }

        // Chuyển đổi encoding sang UTF-8 để hỗ trợ tiếng Việt
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        // Thoát ký tự đặc biệt để tránh lỗi bảo mật khi chạy lệnh shell
        $escapedText = escapeshellarg($text);
        $escapedScript = escapeshellarg($scriptPath);
        $result = '';

        // Thử từng lệnh Python để tìm lệnh hợp lệ
        foreach ($pythonCommands as $pythonPath) {
            // Kiểm tra sự tồn tại của Python trong hệ thống
            $checkCommand = (stripos(PHP_OS, 'WIN') === 0) ? "where $pythonPath 2>&1" : "which $pythonPath 2>&1";
            $pythonExists = shell_exec($checkCommand);
            if (empty($pythonExists)) continue;

            // Thiết lập encoding UTF-8 và chạy script Python với nội dung bình luận
            $command = (stripos(PHP_OS, 'WIN') === 0)
                ? "set PYTHONIOENCODING=utf-8 && $pythonPath $escapedScript $escapedText 2>&1"
                : "PYTHONIOENCODING=utf-8 $pythonPath $escapedScript $escapedText 2>&1";

            // Thực thi lệnh và lấy kết quả
            $output = shell_exec($command);
            $result = trim($output ?? '');

            // Nếu kết quả hợp lệ (không chứa lỗi Python), thoát vòng lặp
            if ($result !== '' && stripos($result, 'Traceback') === false) break;
        }

        // Nếu kết quả rỗng hoặc chứa lỗi Python, ghi log và trả về 1
        // Điều này có thể xảy ra nếu script Python gặp lỗi cú pháp hoặc không chạy được
        if ($result === '' || stripos($result, 'Traceback') !== false) {
            error_log("predictSentiment: Invalid output from Python: '$result'");
            return 1;
        }

        // Xử lý kết quả: trả về 0 (tiêu cực) hoặc 1 (tích cực)
        // Nếu kết quả là số, trả về trực tiếp; nếu là text, kiểm tra từ khóa 'neg'
        if (is_numeric($result)) {
            return ((int)$result === 0) ? 0 : 1;
        }

        return (stripos($result, 'neg') !== false) ? 0 : 1;
        // Liên quan đến toast: Hàm này không gọi toast trực tiếp, nhưng kết quả
        // được sử dụng trong addReview và replyReview để lưu trạng thái bình luận.
        // Nếu script Python lỗi, trạng thái mặc định (1) có thể làm dữ liệu không chính xác,
        // nhưng không ảnh hưởng trực tiếp đến toast.
    }

    /* ---------------------- ADD REVIEW ---------------------- */
    // Thêm một bình luận mới vào bảng reviews và lưu ảnh vào review_images
    public function addReview($user_id, $product_id, $content, $rating = null, $images = null) {
        // Thiết lập ghi log lỗi vào stderr
        ini_set('error_log', 'php://stderr');

        // Dự đoán cảm xúc của nội dung bình luận
        $sentiment = $this->predictSentiment($content);
        error_log("addReview: predicted sentiment = $sentiment");

        // Tạo dữ liệu bình luận để lưu vào Supabase
        $data = [
            'ma_nguoi_dung' => $user_id,
            'ma_san_pham' => $product_id,
            'noi_dung_danh_gia' => $content,
            'diem_danh_gia' => $rating,
            'trang_thai' => $sentiment,
            'thoi_gian_tao' => date('Y-m-d H:i:s'),
            'thoi_gian_cap_nhat' => date('Y-m-d H:i:s'),
            'trang_thai_phan_hoi' => 0
        ];

        // Gửi yêu cầu POST để lưu bình luận vào bảng reviews
        $insert = supabase_request('POST', 'reviews', [], $data);

        // Nếu phản hồi là chuỗi, chuyển thành mảng
        if (is_string($insert)) {
            $insert = json_decode($insert, true);
        }

        // Lấy ID của bình luận vừa thêm
        $ma_danh_gia = $this->getLastInsertedReviewId();

        // Nếu có ảnh và ID hợp lệ, lưu từng ảnh vào bảng review_images
        if ($ma_danh_gia && !empty($images)) {
            if (!is_array($images)) $images = [$images];

            foreach ($images as $img) {
                if (empty($img)) continue;
                $img_data = [
                    'ma_danh_gia' => $ma_danh_gia,
                    'duong_dan_anh' => $img,
                    'thoi_gian_tao' => date('Y-m-d H:i:s'),
                    'thoi_gian_cap_nhat' => date('Y-m-d H:i:s')
                ];
                supabase_request('POST', 'review_images', [], $img_data);
            }
        }

        // Trả về kết quả thành công
        return ['error' => false, 'message' => 'Lưu bình luận thành công'];
        // Liên quan đến toast: Hàm này không được gọi trực tiếp trong các hành động
        // như ẩn, hiện, xóa, hay gửi phản hồi (liên quan đến toast). Nếu thêm tính năng
        // thêm bình luận từ giao diện, kết quả này sẽ được xử lý bởi CommentController
        // để gửi toast_message qua query string. Hiện tại, không ảnh hưởng đến toast.
    }

    /* ---------------------- GET LAST REVIEW ID ---------------------- */
    // Lấy ID của bình luận mới nhất từ bảng reviews
    public function getLastInsertedReviewId() {
        // Gửi yêu cầu GET với sắp xếp giảm dần và giới hạn 1 bản ghi
        $res = supabase_request('GET', 'reviews', [
            'select' => 'ma_danh_gia',
            'order' => 'ma_danh_gia.desc',
            'limit' => 1
        ]);

        // Nếu phản hồi là chuỗi, chuyển thành mảng
        if (is_string($res)) $res = json_decode($res, true);
        return $res['data'][0]['ma_danh_gia'] ?? null;
        // Liên quan đến toast: Hàm này hỗ trợ addReview để lưu ảnh.
        // Nếu Supabase trả về lỗi (null), ảnh không được lưu, nhưng không ảnh hưởng
        // trực tiếp đến toast vì addReview không được gọi trong giao diện quản lý.
    }

    /* ---------------------- GET ALL REVIEWS (FLAT LIST) ---------------------- */
    // Lấy danh sách bình luận (phẳng) với bộ lọc, phân trang, và thông tin liên quan
    public function getAllReviewsFlat($filter = 'all', $rating = null, $replyStatus = null, $page = 1, $perPage = 20) {
        // Thiết lập ghi log lỗi
        ini_set('error_log', 'php://stderr');

        // Xây dựng tham số cho yêu cầu Supabase
        $params = [
            'select' => '*, users:ma_nguoi_dung(ten_nguoi_dung), products:ma_san_pham(ten_san_pham), review_images(ma_hinh_anh, duong_dan_anh)',
            'order' => 'thoi_gian_tao.desc'
        ];

        // Áp dụng bộ lọc
        if ($filter === 'hidden') $params['trang_thai'] = 'eq.0';
        elseif ($filter === 'display') $params['trang_thai'] = 'eq.1';
        elseif ($filter === 'image') {
            // Lọc bình luận có ảnh
            $params['review_images.duong_dan_anh'] = 'not.is.null';
        }

        if (!empty($rating)) $params['diem_danh_gia'] = "eq.$rating";
        if ($replyStatus === '1') $params['trang_thai_phan_hoi'] = 'eq.1';
        elseif ($replyStatus === '0') $params['trang_thai_phan_hoi'] = 'eq.0';

        // Thêm phân trang
        $offset = ($page - 1) * $perPage;
        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        // Gửi yêu cầu GET đến Supabase
        $res = supabase_request('GET', 'reviews', $params);
        if (is_string($res)) $res = json_decode($res, true);

        // Kiểm tra dữ liệu trả về
        if (!isset($res['data']) || !is_array($res['data'])) {
            error_log("getAllReviewsFlat: invalid Supabase response");
            return [];
        }

        // Xử lý dữ liệu ảnh: chuyển review_images thành mảng hinh_anh
        foreach ($res['data'] as &$review) {
            $review['hinh_anh'] = [];
            if (!empty($review['review_images']) && is_array($review['review_images'])) {
                foreach ($review['review_images'] as $img) {
                    if (!empty($img['duong_dan_anh'])) $review['hinh_anh'][] = $img['duong_dan_anh'];
                }
            }
            unset($review['review_images']);
        }

        return $res['data'];
        // Liên quan đến toast: Hàm này cung cấp dữ liệu cho CommentController::index
        // để hiển thị bình luận. Nếu dữ liệu lỗi (mảng rỗng), giao diện có thể không
        // hiển thị bình luận, nhưng không ảnh hưởng trực tiếp đến toast. Dữ liệu sai
        // (như thiếu ma_danh_gia) có thể làm các hành động (ẩn/xóa) thất bại, dẫn đến
        // không gửi được toast_message.
    }

    /* ---------------------- COUNT REVIEWS ---------------------- */
    // Đếm tổng số bình luận theo bộ lọc
    public function countReviews($filter = 'all', $rating = null, $replyStatus = null) {
        // Xây dựng tham số đếm
        $params = ['select' => 'count'];
        if ($filter === 'hidden') $params['trang_thai'] = 'eq.0';
        elseif ($filter === 'display') $params['trang_thai'] = 'eq.1';
        elseif ($filter === 'image') $params['review_images.duong_dan_anh'] = 'not.is.null';
        if (!empty($rating)) $params['diem_danh_gia'] = "eq.$rating";
        if ($replyStatus === '1') $params['trang_thai_phan_hoi'] = 'eq.1';
        elseif ($replyStatus === '0') $params['trang_thai_phan_hoi'] = 'eq.0';

        // Gửi yêu cầu GET để đếm
        $res = supabase_request('GET', 'reviews', $params);
        if (is_string($res)) $res = json_decode($res, true);
        return $res['data'][0]['count'] ?? 0;
        // Liên quan đến toast: Hàm này cung cấp số lượng bình luận cho phân trang.
        // Nếu trả về sai (0), phân trang có thể không hiển thị, nhưng không ảnh hưởng
        // trực tiếp đến toast.
    }

    /* ---------------------- REPLY REVIEW ---------------------- */
    // Thêm phản hồi cho một bình luận
    public function replyReview($review_id, $reply) {
        // Kiểm tra nội dung phản hồi rỗng
        if (empty(trim($reply))) return ['error' => true, 'message' => 'Nội dung trống'];

        // Lấy thông tin bình luận cha
        $res = supabase_request('GET', 'reviews', [
            'select' => 'ma_san_pham, ma_danh_gia_cha',
            'ma_danh_gia' => "eq.$review_id"
        ]);
        if (is_string($res)) $res = json_decode($res, true);

        // Kiểm tra nếu không tìm thấy bình luận cha
        if (empty($res['data'][0])) return ['error' => true, 'message' => 'Không tìm thấy bình luận cha'];

        // Lấy mã sản phẩm từ bình luận cha
        $review = $res['data'][0];
        $ma_san_pham = $review['ma_san_pham'];

        // Dự đoán cảm xúc của phản hồi
        $sentiment = $this->predictSentiment($reply);

        // Tạo dữ liệu phản hồi
        $data = [
            'ma_danh_gia_cha' => $review_id,
            'noi_dung_danh_gia' => $reply,
            'ma_nguoi_dung' => 1, // Giả định người phản hồi là admin (ID=1)
            'ma_san_pham' => $ma_san_pham,
            'diem_danh_gia' => null,
            'trang_thai' => $sentiment,
            'thoi_gian_tao' => date('Y-m-d H:i:s'),
            'thoi_gian_cap_nhat' => date('Y-m-d H:i:s'),
            'trang_thai_phan_hoi' => 1
        ];

        // Lưu phản hồi vào bảng reviews
        supabase_request('POST', 'reviews', [], $data);
        // Cập nhật trạng thái phản hồi của bình luận cha
        supabase_request('PATCH', 'reviews', ['ma_danh_gia' => "eq.$review_id"], [
            'trang_thai_phan_hoi' => 1
        ]);

        return ['error' => false, 'message' => 'Phản hồi thành công'];
        // Liên quan đến toast: Hàm này được gọi khi gửi phản hồi từ giao diện.
        // Kết quả (error/message) được CommentController xử lý để gửi toast_message
        // qua query string. Nếu Supabase lỗi hoặc nội dung rỗng, toast sẽ hiển thị
        // thông báo lỗi (như "Nội dung trống").
    }

    /* ---------------------- CHANGE STATUS ---------------------- */
    // Thay đổi trạng thái bình luận (hiển thị/ẩn/xóa)
    public function changeStatus($id, $status) {
        // Xác định trạng thái mới
        $body = [];
        if ($status === 'display') $body = ['trang_thai' => 1];
        elseif ($status === 'hidden') $body = ['trang_thai' => 0];
        elseif ($status === 'deleted') $body = ['trang_thai' => -1];

        // Gửi yêu cầu PATCH để cập nhật trạng thái
        return supabase_request('PATCH', 'reviews', ['ma_danh_gia' => "eq.$id"], $body);
        // Liên quan đến toast: Hàm này được gọi khi ẩn/hiện/xóa bình luận.
        // CommentController sử dụng kết quả để gửi toast_message (như "Đã ẩn bình luận").
        // Nếu Supabase lỗi, toast có thể không hiển thị đúng nếu controller không xử lý.
    }

    /* ---------------------- DELETE REVIEW + REPLIES ---------------------- */
    // Xóa bình luận và tất cả phản hồi con của nó
    public function deleteReviewAndReplies($id) {
        // Lấy danh sách phản hồi con
        $replies = supabase_request('GET', 'reviews', [
            'select' => 'ma_danh_gia',
            'ma_danh_gia_cha' => "eq.$id"
        ]);
        if (is_string($replies)) $replies = json_decode($replies, true);

        // Xóa đệ quy tất cả phản hồi con
        foreach ($replies['data'] ?? [] as $reply) {
            $this->deleteReviewAndReplies($reply['ma_danh_gia']);
        }

        // Xóa bình luận hiện tại
        return supabase_request('DELETE', 'reviews', ['ma_danh_gia' => "eq.$id"]);
        // Liên quan đến toast: Hàm này được gọi khi xóa bình luận.
        // CommentController gửi toast_message (như "Đã xóa bình luận") dựa trên kết quả.
        // Nếu Supabase lỗi, toast có thể hiển thị thông báo lỗi.
    }

    /* ---------------------- DELETE ALL REVIEWS ---------------------- */
    // Xóa tất cả bình luận trong bảng reviews
    public function deleteAllReviews() {
        // Gửi yêu cầu DELETE để xóa toàn bộ bảng reviews
        $res = supabase_request('DELETE', 'reviews', []);
        if (is_string($res)) $res = json_decode($res, true);
        if (!empty($res['error'])) return ['error' => true, 'message' => 'Xóa lỗi'];
        return ['error' => false, 'message' => 'Đã xóa tất cả'];
        // Liên quan đến toast: Hàm này được gọi khi xóa tất cả bình luận.
        // CommentController gửi toast_message (như "Đã xóa tất cả") dựa trên kết quả.
        // Đây là một trong những hành động trực tiếp liên quan đến toast.
    }
}
?>