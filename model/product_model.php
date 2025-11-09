<?php
require_once __DIR__ . '/../config/supabase.php';

class ProductModel {

	public function getAllProducts($conditions = [], $search = '', $limit = 0, $offset = 0) {
		$queryParams = [
			'select' => 'ma_san_pham,ten_san_pham,mo_ta_san_pham,muc_gia_goc,gia_ban,so_luong_dat_toi_thieu,trang_thai_hien_thi,ngay_tao_ban_ghi,ngay_sua_ban_ghi,ma_danh_muc,ma_bo_suu_tap,san_pham_noi_bat',
			'order' => 'ma_san_pham.desc'
		];

		if (!empty($conditions)) {
			foreach ($conditions as $key => $value) {
				$queryParams[$key] = $value;
			}
		}

		if ($search) {
			$queryParams['ten_san_pham'] = 'ilike.*' . $search . '*';
		}

		if ($limit > 0) $queryParams['limit'] = $limit;
		if ($offset > 0) $queryParams['offset'] = $offset;

		$result = supabase_request('GET', 'products', $queryParams);
		return $result['error'] ? [] : $result['data'];
	}

	public function getTotalProducts($conditions = [], $search = '') {
		$queryParams = ['select' => 'ma_san_pham'];

		if (!empty($conditions)) {
			foreach ($conditions as $key => $value) {
				$queryParams[$key] = $value;
			}
		}

		if ($search) {
			$queryParams['ten_san_pham'] = 'ilike.*' . $search . '*';
		}

		$result = supabase_request('GET', 'products', $queryParams);
		return $result['error'] ? 0 : count($result['data']);
	}

	public function getProductById($ma_san_pham) {
		$result = supabase_request('GET', 'products', [
			'select' => 'ma_san_pham,ten_san_pham,mo_ta_san_pham,muc_gia_goc,gia_ban,so_luong_dat_toi_thieu,trang_thai_hien_thi,ngay_tao_ban_ghi,ngay_sua_ban_ghi,ma_danh_muc,ma_bo_suu_tap,san_pham_noi_bat',
			'ma_san_pham' => 'eq.' . $ma_san_pham
		]);
		return $result['error'] ? [] : ($result['data'][0] ?? []);
	}

    public function createProduct($data) {
        $result = supabase_request('POST', 'products', [], [
			'ten_san_pham' => $data['ten_san_pham'],
			'mo_ta_san_pham' => $data['mo_ta_san_pham'] ?? null,
			'muc_gia_goc' => isset($data['muc_gia_goc']) ? (float)$data['muc_gia_goc'] : null,
			'gia_ban' => isset($data['gia_ban']) ? (float)$data['gia_ban'] : 0,
			'so_luong_dat_toi_thieu' => isset($data['so_luong_dat_toi_thieu']) ? (int)$data['so_luong_dat_toi_thieu'] : 1,
			'trang_thai_hien_thi' => isset($data['trang_thai_hien_thi']) ? (bool)$data['trang_thai_hien_thi'] : true,
			'ngay_tao_ban_ghi' => date('Y-m-d H:i:s'),
			'ngay_sua_ban_ghi' => date('Y-m-d H:i:s'),
            'ma_danh_muc' => isset($data['ma_danh_muc']) && $data['ma_danh_muc'] !== '' ? (int)$data['ma_danh_muc'] : null,
            'ma_bo_suu_tap' => isset($data['ma_bo_suu_tap']) && $data['ma_bo_suu_tap'] !== '' ? (int)$data['ma_bo_suu_tap'] : null,
		]);
        if ($result['error']) return ['success' => false, 'error' => $result['message'] ?? 'API error', 'details' => $result['data'] ?? []];
        $created = $result['data'][0] ?? null;
        return ['success' => true, 'product' => $created];
	}

	public function updateProduct($data) {
		$update = [];
		if (isset($data['ten_san_pham'])) $update['ten_san_pham'] = $data['ten_san_pham'];
		if (isset($data['mo_ta_san_pham'])) $update['mo_ta_san_pham'] = $data['mo_ta_san_pham'];
		if (isset($data['muc_gia_goc'])) $update['muc_gia_goc'] = (float)$data['muc_gia_goc'];
		if (isset($data['gia_ban'])) $update['gia_ban'] = (float)$data['gia_ban'];
		if (isset($data['so_luong_dat_toi_thieu'])) $update['so_luong_dat_toi_thieu'] = (int)$data['so_luong_dat_toi_thieu'];
        if (array_key_exists('ma_danh_muc', $data)) $update['ma_danh_muc'] = $data['ma_danh_muc'] === '' ? null : (int)$data['ma_danh_muc'];
        if (array_key_exists('ma_bo_suu_tap', $data)) $update['ma_bo_suu_tap'] = $data['ma_bo_suu_tap'] === '' ? null : (int)$data['ma_bo_suu_tap'];
		if (isset($data['trang_thai_hien_thi'])) $update['trang_thai_hien_thi'] = (bool)$data['trang_thai_hien_thi'];
		$update['ngay_sua_ban_ghi'] = date('Y-m-d H:i:s');

		$result = supabase_request('PATCH', 'products', ['ma_san_pham' => 'eq.' . $data['ma_san_pham']], $update);
		return !$result['error'];
	}

	public function deleteProduct($ma_san_pham) {
		$result = supabase_request('DELETE', 'products', ['ma_san_pham' => 'eq.' . $ma_san_pham]);
		return !$result['error'];
	}

	public function getVariantsByProduct($ma_san_pham) {
		$res = supabase_request('GET', 'product_variants', [
			'select' => 'ma_bien_the,ma_size,ma_mau,ton_kho',
			'ma_san_pham' => 'eq.' . $ma_san_pham,
			'order' => 'ma_bien_the.asc'
		]);
		return $res['error'] ? [] : $res['data'];
	}

	public function deleteVariantsByProduct($ma_san_pham) {
		$res = supabase_request('DELETE', 'product_variants', [ 'ma_san_pham' => 'eq.' . $ma_san_pham ]);
		return !$res['error'];
	}

	public function createVariant($ma_san_pham, $ma_size, $ma_mau, $ton_kho) {
		$res = supabase_request('POST', 'product_variants', [], [
			'ma_san_pham' => (int)$ma_san_pham,
			'ma_size' => (int)$ma_size,
			'ma_mau' => (int)$ma_mau,
			'ton_kho' => (int)$ton_kho
		]);
		return !$res['error'];
	}

	public function addProductImage($ma_san_pham, $url) {
		$res = supabase_request('POST', 'product_images', [], [
			'ma_san_pham' => (int)$ma_san_pham,
			'duong_dan_anh' => $url
		]);
		return !$res['error'];
	}

	public function deleteImagesByProduct($ma_san_pham) {
		$res = supabase_request('DELETE', 'product_images', [ 'ma_san_pham' => 'eq.' . (int)$ma_san_pham ]);
		return !$res['error'];
	}

	public function getFirstImage($ma_san_pham) {
		$res = supabase_request('GET', 'product_images', [
			'select' => 'duong_dan_anh',
			'ma_san_pham' => 'eq.' . $ma_san_pham,
			'order' => 'ma_hinh_anh.asc',
			'limit' => 1
		]);
		return $res['error'] || empty($res['data']) ? null : $res['data'][0]['duong_dan_anh'];
	}

	public function getAllImagesByProduct($ma_san_pham) {
		$res = supabase_request('GET', 'product_images', [
			'select' => 'ma_hinh_anh,duong_dan_anh',
			'ma_san_pham' => 'eq.' . $ma_san_pham,
			'order' => 'ma_hinh_anh.asc'
		]);
		return $res['error'] ? [] : $res['data'];
	}

	public function deleteImage($ma_hinh_anh) {
		$res = supabase_request('DELETE', 'product_images', [ 'ma_hinh_anh' => 'eq.' . (int)$ma_hinh_anh ]);
		return !$res['error'];
	}

	public function updateFeaturedStatus($ma_san_pham, $isFeatured) {
		$result = supabase_request('PATCH', 'products', [
			'ma_san_pham' => 'eq.' . (int)$ma_san_pham
		], [
			'san_pham_noi_bat' => (bool)$isFeatured,
			'ngay_sua_ban_ghi' => date('Y-m-d H:i:s')
		]);
		return ['error' => $result['error'], 'message' => $result['message'] ?? ''];
	}
}

?>

