<?php
require_once __DIR__ . '/../config/supabase.php';

class VariantModel {
    private $table = 'product_variants';

    // ==================== LẤY DANH SÁCH ====================
    public function getAll($keyword = '', $limit = 10, $page = 1) {
        $offset = ($page - 1) * $limit;
        $query = ['select' => 'ma_bien_the, ton_kho, ma_san_pham, ma_mau, ma_size'];
        $res = supabase_request('GET', $this->table, $query);
        if ($res['error']) return ['data' => [], 'total' => 0];

        $data = $res['data'];

        // Lấy dữ liệu join
        $products = supabase_request('GET', 'products', ['select' => 'ma_san_pham, ten_san_pham']);
        $colors = supabase_request('GET', 'colors', ['select' => 'ma_mau, ten_mau']);
        $sizes = supabase_request('GET', 'sizes', ['select' => 'ma_size, ten_size']);

        $mapProduct = [];
        foreach ($products['data'] ?? [] as $p) $mapProduct[$p['ma_san_pham']] = $p['ten_san_pham'];
        $mapColor = [];
        foreach ($colors['data'] ?? [] as $c) $mapColor[$c['ma_mau']] = $c['ten_mau'];
        $mapSize = [];
        foreach ($sizes['data'] ?? [] as $s) $mapSize[$s['ma_size']] = $s['ten_size'];

        $filtered = [];
        foreach ($data as $v) {
            $tenSP = $mapProduct[$v['ma_san_pham']] ?? '';
            $tenMau = $mapColor[$v['ma_mau']] ?? '';
            $tenSize = $mapSize[$v['ma_size']] ?? '';
            if ($keyword && stripos($tenSP . $tenMau . $tenSize . $v['ma_bien_the'], $keyword) === false) continue;

            $v['ten_san_pham'] = $tenSP;
            $v['ten_mau'] = $tenMau;
            $v['ten_size'] = $tenSize;
            $filtered[] = $v;
        }

        usort($filtered, fn($a, $b) => $b['ma_bien_the'] <=> $a['ma_bien_the']);
        $total = count($filtered);
        $pagedData = array_slice($filtered, $offset, $limit);
        return ['data' => $pagedData, 'total' => $total];
    }

    // ==================== LẤY 1 BIẾN THỂ ====================
    public function getById($id) {
        $res = supabase_request('GET', $this->table, ['ma_bien_the' => "eq.$id"]);
        return $res['data'][0] ?? null;
    }

    // ==================== CẬP NHẬT TOÀN BỘ (ít dùng) ====================
    public function update($data) {
        $payload = [
            'ma_san_pham' => $data['ma_san_pham'],
            'ma_mau' => $data['ma_mau'],
            'ma_size' => $data['ma_size'],
            'ton_kho' => $data['ton_kho']
        ];
        $res = supabase_request('PATCH', $this->table, ['ma_bien_the' => "eq.{$data['ma_bien_the']}"], $payload);
        return !$res['error'];
    }

    // ==================== CẬP NHẬT CHỈ SỐ LƯỢNG ====================
    public function updateQuantity($data) {
        $payload = ['ton_kho' => $data['ton_kho']];
        $res = supabase_request('PATCH', $this->table, ['ma_bien_the' => "eq.{$data['ma_bien_the']}"], $payload);
        return !$res['error'];
    }

    // ==================== THÊM HOẶC CỘNG DỒN ====================
    public function insert($data) {
        $check = supabase_request('GET', $this->table, [
            'ma_san_pham' => "eq.{$data['ma_san_pham']}",
            'ma_mau' => "eq.{$data['ma_mau']}",
            'ma_size' => "eq.{$data['ma_size']}"
        ]);

        // Lấy tên liên quan
        $p = supabase_request('GET', 'products', ['select' => 'ten_san_pham', 'ma_san_pham' => "eq.{$data['ma_san_pham']}"]);
        $c = supabase_request('GET', 'colors', ['select' => 'ten_mau', 'ma_mau' => "eq.{$data['ma_mau']}"]);
        $s = supabase_request('GET', 'sizes', ['select' => 'ten_size', 'ma_size' => "eq.{$data['ma_size']}"]);

        $ten_sp = $p['data'][0]['ten_san_pham'] ?? '';
        $ten_mau = $c['data'][0]['ten_mau'] ?? '';
        $ten_size = $s['data'][0]['ten_size'] ?? '';

        if (!empty($check['data'])) {
            // Biến thể đã tồn tại → cộng dồn tồn kho
            $id = $check['data'][0]['ma_bien_the'];
            $soLuongHienTai = (int)$check['data'][0]['ton_kho'];
            $soLuongThem = (int)$data['ton_kho'];

            $payload = ['ton_kho' => $soLuongHienTai + $soLuongThem];
            supabase_request('PATCH', $this->table, ['ma_bien_the' => "eq.$id"], $payload);

            return [
                'status' => 'update',
                'so_luong_them' => $soLuongThem,
                'ten_sp' => $ten_sp,
                'ten_mau' => $ten_mau,
                'ten_size' => $ten_size
            ];
        } else {
            // Thêm mới
            $payload = [
                'ma_san_pham' => $data['ma_san_pham'],
                'ma_size' => $data['ma_size'],
                'ma_mau' => $data['ma_mau'],
                'ton_kho' => $data['ton_kho']
            ];
            supabase_request('POST', $this->table, [], $payload);

            return [
                'status' => 'insert',
                'ten_sp' => $ten_sp,
                'ten_mau' => $ten_mau,
                'ten_size' => $ten_size
            ];
        }
    }

    // ==================== XÓA ====================
    public function delete($id) {
        $res = supabase_request('DELETE', $this->table, ['ma_bien_the' => "eq.$id"]);
        return !$res['error'];
    }

    // ==================== LẤY DANH SÁCH COMBOBOX ====================
    public function getOptions() {
        $sizes = supabase_request('GET', 'sizes', ['select' => '*']);
        $colors = supabase_request('GET', 'colors', ['select' => '*']);
        $products = supabase_request('GET', 'products', ['select' => 'ma_san_pham, ten_san_pham']);
        return [
            'sizes' => $sizes['data'] ?? [],
            'colors' => $colors['data'] ?? [],
            'products' => $products['data'] ?? []
        ];
    }
}
?>
