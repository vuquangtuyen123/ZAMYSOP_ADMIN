<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once __DIR__ . '/../model/coupon_model.php';
require_once __DIR__ . '/../config/auth.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');

class CouponController {

	private $model;
	private $itemsPerPage = 8; // Phân trang 8 items/trang

	public function __construct() {
		$this->model = new CouponModel();
		if (session_status() === PHP_SESSION_NONE) session_start();
	}

    public function index() {
        require_login();
        // Admin: discount.crud, Moderator: discount.create_edit (view allowed)
		$search = isset($_GET['search']) ? trim($_GET['search']) : '';
		$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
		if (isset($_GET['reset'])) $search = '';

		$total = $this->model->countAll($search);
		$totalPages = max(1, ceil($total / $this->itemsPerPage));
		$coupons = $this->model->getAll($search, $this->itemsPerPage, $page);

		$message = $_SESSION['message'] ?? '';
		unset($_SESSION['message']);

		require_once __DIR__ . '/../view/coupon/index.php';
	}

    public function them() {
        require_login();
        if (!(can('discount.crud') || can('discount.create_edit'))) { http_response_code(403); echo 'Không có quyền.'; exit; }
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			// datetime-local input trả về giá trị dạng "2025-11-07T11:10" (không có timezone)
			// Lưu trực tiếp giá trị này, không convert timezone
			// Chỉ format lại từ "2025-11-07T11:10" thành "2025-11-07 11:10:00"
			$ngay_bat_dau = null;
			$ngay_ket_thuc = null;
			
			if (!empty($_POST['ngay_bat_dau'])) {
				// Format: YYYY-MM-DDTHH:MM -> YYYY-MM-DD HH:MM:SS
				$ngay_bat_dau = str_replace('T', ' ', $_POST['ngay_bat_dau']) . ':00';
			}
			
			if (!empty($_POST['ngay_ket_thuc'])) {
				$ngay_ket_thuc = str_replace('T', ' ', $_POST['ngay_ket_thuc']) . ':00';
			}
			
			// Validation: ngày bắt đầu không được lớn hơn ngày kết thúc
			if ($ngay_bat_dau && $ngay_ket_thuc && strtotime($ngay_bat_dau) > strtotime($ngay_ket_thuc)) {
				$_SESSION['message'] = 'Ngày bắt đầu không được lớn hơn ngày kết thúc';
				header('Location: index.php?c=coupon&a=them');
				exit;
			}
			
			$data = [
				'noi_dung' => $_POST['noi_dung'] ?? null,
				'code' => trim($_POST['code'] ?? ''),
				'mo_ta' => $_POST['mo_ta'] ?? null,
				'loai_giam_gia' => $_POST['loai_giam_gia'] ?? 'percentage',
				'muc_giam_gia' => $_POST['muc_giam_gia'] ?? 0,
				'ngay_bat_dau' => $ngay_bat_dau,
				'ngay_ket_thuc' => $ngay_ket_thuc,
				'so_luong_ban_dau' => $_POST['so_luong_ban_dau'] ?? null,
				'don_gia_toi_thieu' => $_POST['don_gia_toi_thieu'] ?? null,
				'trang_thai_kich_hoat' => isset($_POST['trang_thai_kich_hoat']) ? 1 : 0
			];

            if ($data['code'] === '') {
				$_SESSION['message'] = 'Vui lòng nhập mã giảm giá';
				header('Location: index.php?c=coupon&a=them');
				exit;
			}

			$this->model->create($data);
			$_SESSION['message'] = 'Thêm mã giảm giá thành công';
			header('Location: index.php?c=coupon&a=index');
			exit;
		}

		require_once __DIR__ . '/../view/coupon/them.php';
	}

    public function sua($ma_giam_gia = null) {
        require_login();
        if (!(can('discount.crud') || can('discount.create_edit'))) { http_response_code(403); echo 'Không có quyền.'; exit; }
        if ($ma_giam_gia === null) {
            $ma_giam_gia = isset($_GET['ma_giam_gia']) ? $_GET['ma_giam_gia'] : null;
            if ($ma_giam_gia === null) die('Lỗi: Mã giảm giá không được cung cấp.');
		}

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			// datetime-local input trả về giá trị dạng "2025-11-07T11:10" (không có timezone)
			// Lưu trực tiếp giá trị này, không convert timezone
			// Chỉ format lại từ "2025-11-07T11:10" thành "2025-11-07 11:10:00"
			$ngay_bat_dau = null;
			$ngay_ket_thuc = null;
			
			if (!empty($_POST['ngay_bat_dau'])) {
				// Format: YYYY-MM-DDTHH:MM -> YYYY-MM-DD HH:MM:SS
				$ngay_bat_dau = str_replace('T', ' ', $_POST['ngay_bat_dau']) . ':00';
			}
			
			if (!empty($_POST['ngay_ket_thuc'])) {
				$ngay_ket_thuc = str_replace('T', ' ', $_POST['ngay_ket_thuc']) . ':00';
			}
			
			// Validation: ngày bắt đầu không được lớn hơn ngày kết thúc
			if ($ngay_bat_dau && $ngay_ket_thuc && strtotime($ngay_bat_dau) > strtotime($ngay_ket_thuc)) {
				$_SESSION['message'] = 'Ngày bắt đầu không được lớn hơn ngày kết thúc';
				header('Location: index.php?c=coupon&a=sua&ma_giam_gia=' . $ma_giam_gia);
				exit;
			}
			
			$data = [
                'noi_dung' => $_POST['noi_dung'] ?? null,
                'code' => trim($_POST['code'] ?? ''),
				'mo_ta' => $_POST['mo_ta'] ?? null,
                'loai_giam_gia' => $_POST['loai_giam_gia'] ?? 'percentage',
                'muc_giam_gia' => $_POST['muc_giam_gia'] ?? 0,
				'ngay_bat_dau' => $ngay_bat_dau,
				'ngay_ket_thuc' => $ngay_ket_thuc,
                'so_luong_ban_dau' => $_POST['so_luong_ban_dau'] ?? null,
                'don_gia_toi_thieu' => $_POST['don_gia_toi_thieu'] ?? null,
				'trang_thai_kich_hoat' => isset($_POST['trang_thai_kich_hoat']) ? 1 : 0
			];

            $this->model->update($ma_giam_gia, $data);
			$_SESSION['message'] = 'Cập nhật mã giảm giá thành công';
			header('Location: index.php?c=coupon&a=index');
			exit;
		} else {
            $coupon = $this->model->getById($ma_giam_gia);
			require_once __DIR__ . '/../view/coupon/sua.php';
		}
	}

    public function xoa($ma_giam_gia = null) {
        require_login();
        require_capability('discount.crud');
        if ($ma_giam_gia === null) {
            $ma_giam_gia = isset($_GET['ma_giam_gia']) ? $_GET['ma_giam_gia'] : null;
            if ($ma_giam_gia === null) die('Lỗi: Mã giảm giá không được cung cấp.');
		}
        $this->model->delete($ma_giam_gia);
		$_SESSION['message'] = 'Xóa mã giảm giá thành công';
		header('Location: index.php?c=coupon&a=index');
		exit;
	}

    public function toggleStatus() {
        require_login();
        // Cho phép cả admin và moderator đổi trạng thái
        if (!(can('discount.crud') || can('discount.create_edit'))) { http_response_code(403); echo json_encode(['ok' => false, 'message' => 'Forbidden']); exit; }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok' => false, 'message' => 'Method not allowed']); exit; }

        $ma = isset($_POST['ma_giam_gia']) ? (int)$_POST['ma_giam_gia'] : 0;
        $value = isset($_POST['value']) ? (int)$_POST['value'] : 0; // 1|0
        if ($ma <= 0) { http_response_code(400); echo json_encode(['ok' => false, 'message' => 'Mã không hợp lệ']); exit; }

        $ok = $this->model->update($ma, ['trang_thai_kich_hoat' => $value ? 1 : 0]);
        header('Content-Type: application/json');
        echo json_encode(['ok' => (bool)$ok]);
        exit;
    }
}

?>

