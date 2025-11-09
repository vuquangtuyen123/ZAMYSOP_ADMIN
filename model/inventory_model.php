<?php
require_once __DIR__ . '/../config/supabase.php';

class InventoryModel {

	public function updateVariantStock($ma_bien_the, $ton_kho) {
		$ma_bien_the = (int)$ma_bien_the;
		$ton_kho = (int)$ton_kho;
		$debug = [];
		
		if ($ma_bien_the <= 0) {
			return ['error' => true, 'message' => 'Mã biến thể không hợp lệ', 'debug' => ['ma_bien_the' => $ma_bien_the, 'ton_kho' => $ton_kho]];
		}
		
		$debug['step1'] = 'Bắt đầu cập nhật tồn kho';
		$debug['ma_bien_the'] = $ma_bien_the;
		$debug['ton_kho_input'] = $ton_kho;
		
		// Lấy tồn kho hiện tại - sử dụng format giống order_model.php
		$getParams = [
			'select' => 'ton_kho',
			'ma_bien_the' => "eq.$ma_bien_the"
		];
		$debug['get_params'] = $getParams;
		
		$currentRes = supabase_request('GET', 'product_variants', $getParams);
		$debug['get_response'] = [
			'error' => $currentRes['error'] ?? false,
			'message' => $currentRes['message'] ?? '',
			'data_count' => is_array($currentRes['data'] ?? null) ? count($currentRes['data']) : 0,
			'data' => $currentRes['data'] ?? null
		];
		
		if ($currentRes['error'] || empty($currentRes['data'])) {
			error_log("Lỗi lấy tồn kho cho biến thể #$ma_bien_the: " . ($currentRes['message'] ?? 'Không có dữ liệu'));
			return ['error' => true, 'message' => 'Không tìm thấy biến thể hoặc lỗi khi lấy dữ liệu', 'debug' => $debug];
		}
		
		$currentStock = (int)($currentRes['data'][0]['ton_kho'] ?? 0);
		$newStock = $currentStock + $ton_kho;
		
		$debug['current_stock'] = $currentStock;
		$debug['calculation'] = "$currentStock + $ton_kho = $newStock";
		$debug['new_stock'] = $newStock;
		
		error_log("Cập nhật tồn kho #$ma_bien_the: $currentStock + $ton_kho = $newStock");
		
		// Cập nhật với tồn kho mới (tồn kho hiện tại + số lượng mới)
		$patchParams = [
			'ma_bien_the' => "eq.$ma_bien_the"
		];
		$patchBody = [
			'ton_kho' => $newStock
		];
		$debug['patch_params'] = $patchParams;
		$debug['patch_body'] = $patchBody;
		
		$res = supabase_request('PATCH', 'product_variants', $patchParams, $patchBody);
		
		$debug['patch_response'] = [
			'error' => $res['error'] ?? false,
			'message' => $res['message'] ?? '',
			'status' => $res['status'] ?? null,
			'data' => $res['data'] ?? null
		];
		
		if ($res['error']) {
			error_log("Lỗi cập nhật tồn kho cho #$ma_bien_the: " . ($res['message'] ?? 'Lỗi không xác định'));
			return ['error' => true, 'message' => $res['message'] ?? 'Cập nhật lỗi', 'debug' => $debug];
		}
		
		error_log("Đã cập nhật tồn kho #$ma_bien_the thành công: $currentStock → $newStock");
		$debug['step2'] = 'Cập nhật thành công';
		
		return ['error' => false, 'debug' => $debug];
	}

	/**
	 * Lấy danh sách tồn kho với thông tin sản phẩm
	 */
	public function getInventoryList($search = '', $limit = null, $offset = 0) {
		$params = [
			'select' => 'ma_bien_the,ma_san_pham,ma_size,ma_mau,ton_kho,products(ten_san_pham,ma_danh_muc),sizes(ten_size),colors(ten_mau)',
			'order' => 'ma_bien_the.desc'
		];
		
		if ($limit !== null) {
			$params['limit'] = $limit;
			$params['offset'] = $offset;
		}
		
		$res = supabase_request('GET', 'product_variants', $params);
		if ($res['error']) {
			return ['error' => true, 'data' => []];
		}
		
		$data = $res['data'] ?? [];
		
		// Filter theo search nếu có
		if (!empty($search)) {
			$search_lower = mb_strtolower(trim($search));
			$data = array_filter($data, function($item) use ($search_lower) {
				$product_name = mb_strtolower($item['products']['ten_san_pham'] ?? '');
				$size_name = mb_strtolower($item['sizes']['ten_size'] ?? '');
				$color_name = mb_strtolower($item['colors']['ten_mau'] ?? '');
				$ma_bien_the = (string)$item['ma_bien_the'];
				return strpos($product_name, $search_lower) !== false 
					|| strpos($size_name, $search_lower) !== false 
					|| strpos($color_name, $search_lower) !== false
					|| strpos($ma_bien_the, $search_lower) !== false;
			});
			$data = array_values($data);
		}
		
		return ['error' => false, 'data' => $data];
	}

	public function getTotalInventory($search = '') {
		// Nếu có search, cần filter lại
		if (!empty($search)) {
			$allData = $this->getInventoryList($search);
			return count($allData['data'] ?? []);
		}
		
		// Không có search, lấy tất cả và đếm
		$allData = $this->getInventoryList('', null, 0);
		return count($allData['data'] ?? []);
	}

	/**
	 * Lấy tất cả biến thể để xuất CSV
	 */
	public function getAllVariantsForExport() {
		$params = [
			'select' => 'ma_bien_the,ton_kho',
			'order' => 'ma_bien_the.asc'
		];
		
		$res = supabase_request('GET', 'product_variants', $params);
		return $res['error'] ? ['error' => true, 'data' => []] : ['error' => false, 'data' => $res['data'] ?? []];
	}
}

?>

