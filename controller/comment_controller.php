<?php
require_once __DIR__ . '/../model/comment_model.php';
date_default_timezone_set('Asia/Ho_Chi_Minh'); 

class CommentController {
    private $model;

    public function __construct() {
        $this->model = new CommentModel();
    }

    public function index() {
        $filter = $_GET['filter'] ?? 'all';
        $rating = $_GET['rating'] ?? null;
        $reply_status = $_GET['reply_status'] ?? null;
        $tab = $_GET['tab'] ?? 'reviews';
        $page = max(1, (int)($_GET['page'] ?? 1));

        // Lấy danh sách đánh giá (bao gồm cả cha và con)
        $reviews = $this->model->getAllReviewsFlat($filter, $rating, $reply_status, $page, 20);
        if (!is_array($reviews)) {
            error_log("Invalid reviews data in CommentController::index");
            $reviews = [];
        }

        // Xây dựng cấu trúc cây
        $grouped_reviews = $this->buildReviewTree($reviews);

        // Tab sản phẩm
        if ($tab === 'products') {
            $products = [];
            foreach ($reviews as $r) {
                $pid = $r['ma_san_pham'] ?? null;
                $pname = $r['products']['ten_san_pham'] ?? 'Không rõ';
                if (!$pid || !empty($r['ma_danh_gia_cha'])) continue;

                if (!isset($products[$pid])) {
                    $products[$pid] = [
                        'ten_san_pham' => $pname,
                        'tong_diem' => 0,
                        'so_danh_gia_co_sao' => 0,
                        'tong_binh_luan' => 0
                    ];
                }

                if (!empty($r['diem_danh_gia']) && is_numeric($r['diem_danh_gia'])) {
                    $products[$pid]['tong_diem'] += (float)$r['diem_danh_gia'];
                    $products[$pid]['so_danh_gia_co_sao']++;
                }

                $products[$pid]['tong_binh_luan']++;
            }

            $result = [];
            foreach ($products as $pid => $p) {
                $sao_tb = ($p['so_danh_gia_co_sao'] > 0)
                    ? round($p['tong_diem'] / $p['so_danh_gia_co_sao'], 1)
                    : 0;

                $result[] = [
                    'ten_san_pham' => $p['ten_san_pham'],
                    'so_luong' => $p['tong_binh_luan'],
                    'sao_tb' => $sao_tb
                ];
            }
            usort($result, fn($a, $b) => $b['sao_tb'] <=> $a['sao_tb']);

            $products = $result;
            $grouped_reviews = [];
            require __DIR__ . '/../view/comment/index.php';
            return;
        }

        // Tính tổng số trang
        $total_reviews = $this->model->countReviews($filter, $rating, $reply_status);
        $total_pages = ceil($total_reviews / 20);

        $products = [];
        require __DIR__ . '/../view/comment/index.php';
    }

    // Hàm xây dựng cây bình luận
    private function buildReviewTree($reviews) {
        if (!is_array($reviews)) {
            error_log("Invalid reviews array in buildReviewTree");
            return [];
        }

        $tree = [];
        $map = [];

        // Tạo map để tra cứu nhanh
        foreach ($reviews as $review) {
            if (!is_array($review) || !isset($review['ma_danh_gia'])) {
                error_log("Invalid review data in buildReviewTree: " . json_encode($review));
                continue;
            }
            $review['replies'] = [];
            $review['total_replies'] = $this->countTotalReplies($review, $reviews);
            $map[$review['ma_danh_gia']] = $review;
        }

        // Gắn các bình luận con vào cha
        foreach ($map as $id => $review) {
            if (!empty($review['ma_danh_gia_cha']) && isset($map[$review['ma_danh_gia_cha']])) {
                $map[$review['ma_danh_gia_cha']]['replies'][] = &$map[$id];
            } else {
                $tree[] = &$map[$id];
            }
        }

        return $tree;
    }

    // Hàm đếm tổng số phản hồi con đệ quy (chỉ đếm trạng thái hiển thị)
    private function countTotalReplies($review, $all_reviews) {
        $count = 0;
        foreach ($all_reviews as $r) {
            if ($r['ma_danh_gia_cha'] == $review['ma_danh_gia'] && $r['trang_thai'] == 1) {
                $count++;
                $count += $this->countTotalReplies($r, $all_reviews);
            }
        }
        return $count;
    }

    public function reply() {
        $toast_message = 'Phản hồi thành công';
        $toast_type = 'success';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['reply'])) {
            $review_id = $_POST['review_id'];
            $reply = trim($_POST['reply']);
            $result = $this->model->replyReview($review_id, $reply);
            if ($result['error']) {
                error_log("Reply failed: " . $result['message']);
                $toast_message = $result['message'] ?: 'Gửi phản hồi thất bại';
                $toast_type = 'error';
            }
        } else {
            $toast_message = 'Nội dung phản hồi trống';
            $toast_type = 'error';
        }
        $query = $_GET;
        $query['a'] = 'index';
        $query['toast_message'] = urlencode($toast_message);
        $query['toast_type'] = $toast_type;
        header('Location: index.php?' . http_build_query($query));
        exit();
    }

    public function change_status() {
        $id = $_GET['review_id'] ?? null;
        $status = $_GET['status'] ?? null;
        $toast_message = '';
        $toast_type = 'success';
        if ($id && in_array($status, ['display', 'hidden', 'deleted'])) {
            if ($status === 'deleted') {
                $result = $this->model->deleteReviewAndReplies($id);
                if (!empty($result['error'])) {
                    error_log("Delete failed: " . $result['message']);
                    $toast_message = $result['message'] ?: 'Xóa thất bại';
                    $toast_type = 'error';
                } else {
                    $toast_message = 'Đã xóa bình luận';
                }
            } else {
                $this->model->changeStatus($id, $status);
                $toast_message = ($status === 'display') ? 'Đã hiện bình luận' : 'Đã ẩn bình luận';
            }
        } else {
            $toast_message = 'Thao tác không hợp lệ';
            $toast_type = 'error';
        }
        $query = $_GET;
        $query['a'] = 'index';
        $query['toast_message'] = urlencode($toast_message);
        $query['toast_type'] = $toast_type;
        header('Location: index.php?' . http_build_query($query));
        exit();
    }

    public function delete_all() {
        $result = $this->model->deleteAllReviews();
        $toast_message = $result['error'] ? ($result['message'] ?: 'Xóa tất cả thất bại') : 'Đã xóa tất cả đánh giá';
        $toast_type = $result['error'] ? 'error' : 'success';
        if (!empty($result['error'])) {
            error_log("Delete all failed: " . $result['message']);
        }
        $query = $_GET;
        $query['a'] = 'index';
        $query['toast_message'] = urlencode($toast_message);
        $query['toast_type'] = $toast_type;
        header('Location: index.php?' . http_build_query($query));
        exit();
    }
}