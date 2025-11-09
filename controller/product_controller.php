<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once __DIR__ . '/../model/product_model.php';
require_once __DIR__ . '/../model/danhmuc_model.php';
require_once __DIR__ . '/../model/size_model.php';
require_once __DIR__ . '/../model/color_model.php';
require_once __DIR__ . '/../config/supabase.php';
require_once __DIR__ . '/../config/auth.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');

class ProductController {

	private $model;
	private $categoryModel;
	private $sizeModel;
	private $colorModel;
	private $itemsPerPage = 8; // Phân trang 8 items/trang

	public function __construct() {
		$this->model = new ProductModel();
		$this->categoryModel = new CategoryModel();
		$this->sizeModel = new SizeModel();
		$this->colorModel = new ColorModel();
		if (session_status() === PHP_SESSION_NONE) session_start();
	}

    public function index() {
        require_login();
        if (!(can('product.crud') || can('product.edit'))) { http_response_code(403); echo 'Không có quyền.'; exit; }
		$search = isset($_GET['search']) ? trim($_GET['search']) : '';
		$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
		if (isset($_GET['reset'])) $search = '';

		$offset = ($page - 1) * $this->itemsPerPage;
		$total = $this->model->getTotalProducts([], $search);
		$totalPages = max(1, ceil($total / $this->itemsPerPage));
		$products = $this->model->getAllProducts([], $search, $this->itemsPerPage, $offset);

		$categoryMap = [];
		$dm = $this->categoryModel->layTatCaDanhMuc();
		if ($dm['success']) {
			foreach ($dm['data'] as $cat) {
				$categoryMap[$cat['ma_danh_muc']] = $cat['ten_danh_muc'];
			}
		}

		$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
		unset($_SESSION['message']);

		require_once __DIR__ . '/../view/product/index.php';
	}

    public function them() {
        require_login();
        if (!(can('product.crud') || can('product.edit'))) { http_response_code(403); echo 'Không có quyền.'; exit; }
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$data = [
                'ten_san_pham' => $_POST['ten_san_pham'] ?? '',
                'mo_ta_san_pham' => $_POST['mo_ta_san_pham'] ?? null,
				'muc_gia_goc' => ($_POST['muc_gia_goc'] ?? '') === '' ? null : $_POST['muc_gia_goc'],
				'gia_ban' => ($_POST['gia_ban'] ?? '') === '' ? 0 : $_POST['gia_ban'],
				'so_luong_dat_toi_thieu' => ($_POST['so_luong_dat_toi_thieu'] ?? '') === '' ? 1 : $_POST['so_luong_dat_toi_thieu'],
				'ma_danh_muc' => ($_POST['ma_danh_muc'] ?? '') === '' ? null : $_POST['ma_danh_muc'],
                'trang_thai_hien_thi' => isset($_POST['trang_thai_hien_thi']) ? 1 : 0,
				'ma_bo_suu_tap' => ($_POST['ma_bo_suu_tap'] ?? '') === '' ? null : $_POST['ma_bo_suu_tap'],
			];

			// Validate inputs tối thiểu
			$errs = [];
			if (trim($data['ten_san_pham']) === '') $errs[] = 'Tên sản phẩm là bắt buộc';
			if (!is_numeric($data['gia_ban'])) $errs[] = 'Giá bán không hợp lệ';
			if (empty($data['ma_danh_muc'])) $errs[] = 'Vui lòng chọn danh mục';
			if (!empty($errs)) {
				$error = implode('. ', $errs);
				$dm = $this->categoryModel->layTatCaDanhMuc();
				$danh_sach_danh_muc = $dm['success'] ? $dm['data'] : [];
				$sizes = $this->sizeModel->getAllSizes();
				$colors = $this->colorModel->getAllColors();
				require_once __DIR__ . '/../view/product/them.php';
				return;
			}

			$created = $this->model->createProduct($data);
			if (!$created['success'] || empty($created['product']['ma_san_pham'])) {
				$detail = isset($created['details']) ? (' (' . (is_string($created['details']) ? $created['details'] : json_encode($created['details']))) . ')' : '';
				$error = 'Không thể tạo sản phẩm: ' . ($created['error'] ?? 'Lỗi không xác định') . $detail;
				$dm = $this->categoryModel->layTatCaDanhMuc();
				$danh_sach_danh_muc = $dm['success'] ? $dm['data'] : [];
				$sizes = $this->sizeModel->getAllSizes();
				$colors = $this->colorModel->getAllColors();
				require_once __DIR__ . '/../view/product/them.php';
				return;
			}

			$productId = $created['product']['ma_san_pham'];

			// Upload images (multiple)
			if (!empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
				$count = count($_FILES['images']['name']);
				for ($i = 0; $i < $count; $i++) {
					if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
						$tmp = $_FILES['images']['tmp_name'][$i];
						$orig = basename($_FILES['images']['name'][$i]);
						$safe = uniqid('p' . $productId . '_') . '-' . preg_replace('/[^a-zA-Z0-9-_\.]/', '', $orig);
						$up = supabase_storage_upload('product-images', $safe, $tmp);
						if (empty($up['error'])) {
							global $SUPABASE_STORAGE_URL;
							$url = $SUPABASE_STORAGE_URL . '/object/product-images/' . rawurlencode($safe);
							$this->model->addProductImage($productId, $url);
						}
					}
				}
			}

			// Variants
			$variantColors = $_POST['variant_color'] ?? [];
			$variantSizes  = $_POST['variant_size'] ?? [];
			$variantStocks = $_POST['variant_stock'] ?? [];
			for ($i = 0; $i < count($variantColors); $i++) {
				$col = (int)($variantColors[$i] ?? 0);
				$sz  = (int)($variantSizes[$i] ?? 0);
				$st  = (int)($variantStocks[$i] ?? 0);
				if ($col && $sz) { $this->model->createVariant($productId, $sz, $col, max(0, $st)); }
			}

			$_SESSION['message'] = 'Thêm sản phẩm thành công';
			header('Location: index.php?c=product&a=index');
			exit;
		} else {
			$dm = $this->categoryModel->layTatCaDanhMuc();
			$danh_sach_danh_muc = $dm['success'] ? $dm['data'] : [];
			$sizes = $this->sizeModel->getAllSizes();
			$colors = $this->colorModel->getAllColors();
			require_once __DIR__ . '/../view/product/them.php';
		}
	}

    public function sua($ma_san_pham = null) {
        require_login();
        if (!(can('product.crud') || can('product.edit'))) { http_response_code(403); echo 'Không có quyền.'; exit; }
		if ($ma_san_pham === null) {
			$ma_san_pham = isset($_GET['ma_san_pham']) ? $_GET['ma_san_pham'] : null;
			if ($ma_san_pham === null) die('Lỗi: Mã sản phẩm không được cung cấp.');
		}

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$product = $this->model->getProductById($ma_san_pham);
            $data = [
				'ma_san_pham' => $ma_san_pham,
                'ten_san_pham' => $_POST['ten_san_pham'] ?? '',
                'mo_ta_san_pham' => $_POST['mo_ta_san_pham'] ?? null,
                'muc_gia_goc' => $_POST['muc_gia_goc'] ?? null,
                'gia_ban' => $_POST['gia_ban'] ?? 0,
                'so_luong_dat_toi_thieu' => $_POST['so_luong_dat_toi_thieu'] ?? 1,
                'ma_danh_muc' => $_POST['ma_danh_muc'] ?? null,
                'trang_thai_hien_thi' => isset($_POST['trang_thai_hien_thi']) ? 1 : 0,
                'ma_bo_suu_tap' => $_POST['ma_bo_suu_tap'] ?? null,
			];

			$this->model->updateProduct($data);
			// If new images uploaded, add them (don't delete existing)
			if (!empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
				$count = count($_FILES['images']['name']);
				for ($i = 0; $i < $count; $i++) {
					if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
						$tmp = $_FILES['images']['tmp_name'][$i];
						$orig = basename($_FILES['images']['name'][$i]);
						$safe = uniqid('p' . $ma_san_pham . '_') . '-' . preg_replace('/[^a-zA-Z0-9-_\.]/', '', $orig);
						$up = supabase_storage_upload('product-images', $safe, $tmp);
						if (empty($up['error'])) {
							global $SUPABASE_STORAGE_URL;
							$url = $SUPABASE_STORAGE_URL . '/object/product-images/' . rawurlencode($safe);
							$this->model->addProductImage($ma_san_pham, $url);
						}
					}
				}
			}
			// variants: recreate from form
			$this->model->deleteVariantsByProduct($ma_san_pham);
			$variantColors = $_POST['variant_color'] ?? [];
			$variantSizes  = $_POST['variant_size'] ?? [];
			$variantStocks = $_POST['variant_stock'] ?? [];
			for ($i = 0; $i < count($variantColors); $i++) {
				$col = (int)($variantColors[$i] ?? 0);
				$sz  = (int)($variantSizes[$i] ?? 0);
				$st  = (int)($variantStocks[$i] ?? 0);
				if ($col && $sz) { $this->model->createVariant($ma_san_pham, $sz, $col, max(0, $st)); }
			}
			$_SESSION['message'] = 'Cập nhật sản phẩm thành công';
			header('Location: index.php?c=product&a=index');
			exit;
		} else {
			$product = $this->model->getProductById($ma_san_pham);
			$dm = $this->categoryModel->layTatCaDanhMuc();
			$danh_sach_danh_muc = $dm['success'] ? $dm['data'] : [];
			$sizes = $this->sizeModel->getAllSizes();
			$colors = $this->colorModel->getAllColors();
			$variants = $this->model->getVariantsByProduct($ma_san_pham);
			$images = $this->model->getAllImagesByProduct($ma_san_pham);
			require_once __DIR__ . '/../view/product/sua.php';
		}
	}

	public function xoaImage() {
		require_login();
		if (!(can('product.crud') || can('product.edit'))) { http_response_code(403); echo 'Không có quyền.'; exit; }
		$ma_hinh_anh = (int)($_GET['ma_hinh_anh'] ?? 0);
		if ($ma_hinh_anh) {
			$this->model->deleteImage($ma_hinh_anh);
			$_SESSION['message'] = 'Xóa hình ảnh thành công';
		}
		$ma_san_pham = (int)($_GET['ma_san_pham'] ?? 0);
		if ($ma_san_pham) {
			header('Location: index.php?c=product&a=sua&ma_san_pham=' . $ma_san_pham);
		} else {
			header('Location: index.php?c=product&a=index');
		}
		exit;
	}

    public function xoa($ma_san_pham = null) {
        require_login();
        require_capability('product.crud');
		if ($ma_san_pham === null) {
			$ma_san_pham = isset($_GET['ma_san_pham']) ? $_GET['ma_san_pham'] : null;
			if ($ma_san_pham === null) die('Lỗi: Mã sản phẩm không được cung cấp.');
		}

		$this->model->deleteProduct($ma_san_pham);
		$_SESSION['message'] = 'Xóa sản phẩm thành công';
		header('Location: index.php?c=product&a=index');
		exit;
	}

	public function toggleFeatured() {
		require_login();
		
		// Đảm bảo không có output trước JSON
		if (ob_get_level() > 0) {
			ob_clean();
		}
		header('Content-Type: application/json; charset=UTF-8');
		
		if (!(can('product.crud') || can('product.edit'))) {
			http_response_code(403);
			echo json_encode(['success' => false, 'message' => 'Không có quyền']);
			exit;
		}

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
			exit;
		}

		$ma_san_pham = (int)($_POST['ma_san_pham'] ?? 0);
		$isFeatured = filter_var($_POST['is_featured'] ?? false, FILTER_VALIDATE_BOOLEAN);

		if ($ma_san_pham <= 0) {
			echo json_encode(['success' => false, 'message' => 'Mã sản phẩm không hợp lệ']);
			exit;
		}

		$result = $this->model->updateFeaturedStatus($ma_san_pham, $isFeatured);

		if ($result['error']) {
			echo json_encode([
				'success' => false,
				'message' => $result['message'] ?? 'Không thể cập nhật trạng thái nổi bật'
			]);
		} else {
			echo json_encode([
				'success' => true,
				'message' => 'Cập nhật thành công'
			]);
		}
		exit;
	}
}

?>

