<?php
require_once __DIR__ . '/../config/supabase.php';

class UserModel {

	public function getAll($search = '', $limit = 10, $page = 1, $role = 0) {
		$offset = ($page - 1) * $limit;
		$params = [
			'select' => 'id,ten_nguoi_dung,email,so_dien_thoai,ma_role,created_at,updated_at',
			'order' => 'created_at.desc',
			'limit' => $limit,
			'offset' => $offset
		];
		if ($search !== '') $params['ten_nguoi_dung'] = 'ilike.*' . $search . '*';
		if ($role > 0) $params['ma_role'] = 'eq.' . $role;

		$res = supabase_request('GET', 'users', $params);
		return $res['error'] ? [] : $res['data'];
	}

	public function countAll($search = '', $role = 0) {
		$params = ['select' => 'id'];
		if ($search !== '') $params['ten_nguoi_dung'] = 'ilike.*' . $search . '*';
		if ($role > 0) $params['ma_role'] = 'eq.' . $role;

		$res = supabase_request('GET', 'users', $params);
		return $res['error'] ? 0 : count($res['data']);
	}

	public function getById($id) {
		$res = supabase_request('GET', 'users', ['select' => '*', 'id' => 'eq.' . (int)$id]);
		return $res['error'] ? [] : ($res['data'][0] ?? []);
	}

	public function create($data) {
		$body = [
			'ten_nguoi_dung' => $data['ten_nguoi_dung'],
			'email' => $data['email'],
			'so_dien_thoai' => $data['so_dien_thoai'] ?? null,
			'ma_role' => (int)$data['ma_role'],
			'mat_khau' => $data['email'],
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		];
		$res = supabase_request('POST', 'users', [], $body);
		return !$res['error'];
	}

	public function update($id, $data) {
		$update = [];
		foreach (['ten_nguoi_dung','email','so_dien_thoai'] as $k) if (isset($data[$k])) $update[$k] = $data[$k];
		if (isset($data['ma_role'])) $update['ma_role'] = (int)$data['ma_role'];
		if (isset($data['mat_khau'])) $update['mat_khau'] = (string)$data['mat_khau'];

		try {
			$curr = supabase_request('GET', 'users', ['select' => 'id,email,ma_role', 'id' => 'eq.' . (int)$id]);
			if (!$curr['error'] && !empty($curr['data'])) {
				$before = (int)($curr['data'][0]['ma_role'] ?? 3);
				$after  = isset($update['ma_role']) ? (int)$update['ma_role'] : $before;
				if (in_array($after, [1,2], true) && $after !== $before) {
					$emailNow = $update['email'] ?? ($curr['data'][0]['email'] ?? null);
					if ($emailNow) { $update['mat_khau'] = $emailNow; }
				}
			}
		} catch (Exception $e) { /* ignore */ }

		$update['updated_at'] = date('Y-m-d H:i:s');
		$res = supabase_request('PATCH', 'users', ['id' => 'eq.' . (int)$id], $update);
		return !$res['error'];
	}

	public function delete($id) {
		$res = supabase_request('DELETE', 'users', ['id' => 'eq.' . (int)$id]);
		return !$res['error'];
	}
}
?>
