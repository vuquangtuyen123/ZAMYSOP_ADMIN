<?php
require_once __DIR__ . '/../model/user_model.php';
require_once __DIR__ . '/../config/auth.php';

class UserController {

	private $model;
	private $itemsPerPage = 8; // Phân trang 8 items/trang

	public function __construct() {
		$this->model = new UserModel();
		if (session_status() === PHP_SESSION_NONE) session_start();
	}

	public function index() {
		require_login();
		if (!(can('user.manage_staff_and_customers') || can('user.view_customers'))) { http_response_code(403); echo 'Không có quyền.'; exit; }

		$search = trim($_GET['search'] ?? '');
		$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
		$role = isset($_GET['role']) ? (int)$_GET['role'] : 0; // lọc theo vai trò

		$total = $this->model->countAll($search, $role);
		$totalPages = max(1, ceil($total / $this->itemsPerPage));
		$users = $this->model->getAll($search, $this->itemsPerPage, $page, $role);

		require_once __DIR__ . '/../view/user/index.php';
	}

	public function them() {
		require_login();
		require_capability('user.manage_staff_and_customers');
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$data = [
				'ten_nguoi_dung' => $_POST['ten_nguoi_dung'] ?? '',
				'email' => $_POST['email'] ?? '',
				'so_dien_thoai' => $_POST['so_dien_thoai'] ?? null,
				'ma_role' => $_POST['ma_role'] ?? 3,
			];
			$this->model->create($data);
			header('Location: index.php?c=user&a=index'); exit;
		}
		require_once __DIR__ . '/../view/user/them.php';
	}

	public function sua($id = null) {
		require_login();
		require_capability('user.manage_staff_and_customers');
		$id = $id ?? ($_GET['id'] ?? null);
		if (!$id) { echo 'Thiếu ID'; exit; }
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$data = [
				'ten_nguoi_dung' => $_POST['ten_nguoi_dung'] ?? '',
				'email' => $_POST['email'] ?? '',
				'so_dien_thoai' => $_POST['so_dien_thoai'] ?? null,
				'ma_role' => $_POST['ma_role'] ?? 3,
			];
			$this->model->update($id, $data);
			header('Location: index.php?c=user&a=index'); exit;
		}
		$user = $this->model->getById($id);
		require_once __DIR__ . '/../view/user/sua.php';
	}

	public function xoa($id = null) {
		require_login();
		require_capability('user.manage_staff_and_customers');
		$id = $id ?? ($_GET['id'] ?? null);
		if (!$id) { echo 'Thiếu ID'; exit; }
		$this->model->delete($id);
		header('Location: index.php?c=user&a=index'); exit;
	}
}
?>
