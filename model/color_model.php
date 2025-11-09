<?php
require_once __DIR__ . '/../config/supabase.php';

class ColorModel {

	public function getAllColors($limit = null, $offset = 0) {
		$params = [ 'select' => 'ma_mau,ten_mau,ma_mau_hex', 'order' => 'ma_mau.desc' ];
		if ($limit !== null) {
			$params['limit'] = $limit;
			$params['offset'] = $offset;
		}
		$res = supabase_request('GET', 'colors', $params);
		return $res['error'] ? [] : $res['data'];
	}

	public function getTotalColors() {
		$res = supabase_request('GET', 'colors', [ 'select' => 'ma_mau', 'count' => 'exact' ]);
		return $res['error'] ? 0 : (int)($res['count'] ?? count($res['data'] ?? []));
	}

	public function create($ten_mau, $ma_mau_hex) {
		$res = supabase_request('POST', 'colors', [], [ 'ten_mau' => $ten_mau, 'ma_mau_hex' => $ma_mau_hex ]);
		return !$res['error'];
	}

	public function update($ma_mau, $ten_mau, $ma_mau_hex) {
		$res = supabase_request('PATCH', 'colors', [ 'ma_mau' => 'eq.' . (int)$ma_mau ], [ 'ten_mau' => $ten_mau, 'ma_mau_hex' => $ma_mau_hex ]);
		return !$res['error'];
	}

	public function delete($ma_mau) {
		$res = supabase_request('DELETE', 'colors', [ 'ma_mau' => 'eq.' . (int)$ma_mau ]);
		return !$res['error'];
	}
}

?>


