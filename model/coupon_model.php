<?php
require_once __DIR__ . '/../config/supabase.php';

class CouponModel {

	public function getAll($search = '', $limit = 10, $page = 1) {
		$offset = ($page - 1) * $limit;
        $params = [
            'select' => 'ma_giam_gia,noi_dung,code,mo_ta,loai_giam_gia,muc_giam_gia,ngay_bat_dau,ngay_ket_thuc,trang_thai_kich_hoat,so_luong_ban_dau,so_luong_da_dung,don_gia_toi_thieu',
            'order' => 'ma_giam_gia.desc',
			'limit' => $limit,
			'offset' => $offset
		];
		if ($search !== '') {
            $params['code'] = 'ilike.*' . $search . '*';
		}
        $res = supabase_request('GET', 'discounts', $params);
		return $res['error'] ? [] : $res['data'];
	}

	public function countAll($search = '') {
        $params = ['select' => 'ma_giam_gia'];
        if ($search !== '') $params['code'] = 'ilike.*' . $search . '*';
        $res = supabase_request('GET', 'discounts', $params);
		return $res['error'] ? 0 : count($res['data']);
	}

    public function getById($ma_giam_gia) {
        $res = supabase_request('GET', 'discounts', [
            'select' => '*',
            'ma_giam_gia' => 'eq.' . $ma_giam_gia
        ]);
		return $res['error'] ? [] : ($res['data'][0] ?? []);
	}

    public function create($data) {
        $body = [
            // ma_giam_gia auto-increment by DB
            'noi_dung' => $data['noi_dung'] ?? null,
            'code' => $data['code'],
            'mo_ta' => $data['mo_ta'] ?? null,
            'loai_giam_gia' => $data['loai_giam_gia'], // 'percentage' | 'fixed'
            'muc_giam_gia' => (float)$data['muc_giam_gia'],
            'ngay_bat_dau' => $data['ngay_bat_dau'] ?? null,
            'ngay_ket_thuc' => $data['ngay_ket_thuc'] ?? null,
            'trang_thai_kich_hoat' => isset($data['trang_thai_kich_hoat']) ? (bool)$data['trang_thai_kich_hoat'] : true,
            'so_luong_ban_dau' => isset($data['so_luong_ban_dau']) ? (int)$data['so_luong_ban_dau'] : null,
            'so_luong_da_dung' => 0,
            'don_gia_toi_thieu' => isset($data['don_gia_toi_thieu']) ? (float)$data['don_gia_toi_thieu'] : null,
        ];
        $res = supabase_request('POST', 'discounts', [], $body);
		return !$res['error'];
	}

    public function update($ma_giam_gia, $data) {
		$update = [];
        foreach (['noi_dung','code','mo_ta','loai_giam_gia'] as $k) if (isset($data[$k])) $update[$k] = $data[$k];
        if (isset($data['muc_giam_gia'])) $update['muc_giam_gia'] = (float)$data['muc_giam_gia'];
        foreach (['ngay_bat_dau','ngay_ket_thuc'] as $k) if (isset($data[$k])) $update[$k] = $data[$k];
        if (isset($data['so_luong_ban_dau'])) $update['so_luong_ban_dau'] = (int)$data['so_luong_ban_dau'];
        if (isset($data['don_gia_toi_thieu'])) $update['don_gia_toi_thieu'] = (float)$data['don_gia_toi_thieu'];
		if (isset($data['trang_thai_kich_hoat'])) $update['trang_thai_kich_hoat'] = (bool)$data['trang_thai_kich_hoat'];
        $res = supabase_request('PATCH', 'discounts', ['ma_giam_gia' => 'eq.' . $ma_giam_gia], $update);
		return !$res['error'];
	}

    public function delete($ma_giam_gia) {
        $res = supabase_request('DELETE', 'discounts', ['ma_giam_gia' => 'eq.' . $ma_giam_gia]);
		return !$res['error'];
	}
}

?>

