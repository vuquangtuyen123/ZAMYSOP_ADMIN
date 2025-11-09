<?php
require_once __DIR__ . '/../config/supabase.php';

class SizeModel {

	public function getAllSizes($limit = null, $offset = 0) {
		$params = [ 'select' => 'ma_size,ten_size', 'order' => 'ma_size.desc' ];
		if ($limit !== null) {
			$params['limit'] = $limit;
			$params['offset'] = $offset;
		}
		$res = supabase_request('GET', 'sizes', $params);
		return $res['error'] ? [] : $res['data'];
	}

	public function getTotalSizes() {
		$res = supabase_request('GET', 'sizes', [ 'select' => 'ma_size', 'count' => 'exact' ]);
		return $res['error'] ? 0 : (int)($res['count'] ?? count($res['data'] ?? []));
	}

	public function create($ten_size) {
		$res = supabase_request('POST', 'sizes', [], [ 'ten_size' => $ten_size ]);
		return !$res['error'];
	}

	public function update($ma_size, $ten_size) {
		$res = supabase_request('PATCH', 'sizes', [ 'ma_size' => 'eq.' . (int)$ma_size ], [ 'ten_size' => $ten_size ]);
		return !$res['error'];
	}

	public function delete($ma_size) {
		$res = supabase_request('DELETE', 'sizes', [ 'ma_size' => 'eq.' . (int)$ma_size ]);
		return !$res['error'];
	}
}

?>


