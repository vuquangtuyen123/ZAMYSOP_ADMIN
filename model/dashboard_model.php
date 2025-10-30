<?php
require_once __DIR__ . '/../config/supabase.php';

class DashboardModel {

    /** 
     * === HÀM PHỤ: KIỂM TRA ĐƠN HÀNG HỢP LỆ THEO LỌC NGÀY/THÁNG/NĂM ===
     * @param array $order Thông tin đơn hàng
     * @param string|null $loaiLoc Kiểu lọc: 'day' | 'month' | 'year'
     * @param string|null $giaTri Giá trị lọc (VD: 2025-10-30)
     * @return bool true nếu đơn hàng thỏa mãn bộ lọc thời gian
     */
    private static function donHangHopLe(array $order, ?string $loaiLoc, ?string $giaTri): bool {
        if (!$loaiLoc || !$giaTri) return true; // Không lọc
        $ngayRaw = $order['ngay_dat_hang'] ?? '';
        if (!$ngayRaw) return false;

        try {
            $dt = new DateTime(substr($ngayRaw, 0, 10)); // chỉ lấy phần YYYY-MM-DD
        } catch (Exception $e) {
            return false;
        }

        return match ($loaiLoc) {
            'day'   => $dt->format('Y-m-d') === $giaTri,
            'month' => $dt->format('Y-m')   === $giaTri,
            'year'  => $dt->format('Y')     === $giaTri,
            default => true,
        };
    }

    /**
     * === 1. LẤY TỔNG QUAN ===
     * Lấy số liệu tổng: tổng đơn, đã thanh toán, chưa thanh toán, đơn hủy, đơn hoàn, tổng doanh thu.
     * Doanh thu KHÔNG tính đơn bị hủy hoặc hoàn.
     */
    public static function layTongQuan(?string $loaiLoc = null, ?string $giaTri = null): array {
        $res = supabase_request('GET', 'orders', [
            'select' => 'ma_don_hang, tong_gia_tri_don_hang, ma_trang_thai_don_hang, trang_thai_thanh_toan, ngay_dat_hang'
        ]);

        // Nếu lỗi hoặc không có dữ liệu
        if ($res['error'] || !is_array($res['data'])) {
            return [
                'tong_don_hang'   => 0,
                'da_thanh_toan'   => 0,
                'chua_thanh_toan' => 0,
                'don_huy'         => 0,
                'don_hoan'        => 0,
                'tong_doanh_thu'  => 0
            ];
        }

        // Biến đếm
        $tong = $daThanhToan = $chuaThanhToan = $donHuy = $donHoan = 0;
        $doanhThuTong = 0;

        foreach ($res['data'] as $o) {
            if (!self::donHangHopLe($o, $loaiLoc, $giaTri)) continue; // bỏ qua nếu không thỏa điều kiện lọc

            $tong++;
            $maTrangThai = (int)($o['ma_trang_thai_don_hang'] ?? 0);
            $trangThaiThanhToan = $o['trang_thai_thanh_toan'] ?? '';
            $tongGia = (float)($o['tong_gia_tri_don_hang'] ?? 0);

            // Đơn hủy hoặc hoàn: chỉ tính số lượng, KHÔNG cộng doanh thu
            if ($maTrangThai === 5) { // 5 = hủy
                $donHuy++;
                continue;
            }
            if ($maTrangThai === 6) { // 6 = hoàn
                $donHoan++;
                continue;
            }

            // Tổng doanh thu: chỉ tính đơn không bị hủy, hoàn
            $doanhThuTong += $tongGia;

            // Phân loại thanh toán
            if ($trangThaiThanhToan === 'da_thanh_toan') {
                $daThanhToan++;
            } elseif ($trangThaiThanhToan === 'chua_thanh_toan') {
                $chuaThanhToan++;
            }
        }

        // Trả kết quả
        return [
            'tong_don_hang'   => $tong,
            'da_thanh_toan'   => $daThanhToan,
            'chua_thanh_toan' => $chuaThanhToan,
            'don_huy'         => $donHuy,
            'don_hoan'        => $donHoan,
            'tong_doanh_thu'  => (int)round($doanhThuTong)
        ];
    }

    /**
     * === 2. LẤY DOANH THU THEO THỜI GIAN ===
     * Trả về danh sách doanh thu theo ngày/tháng/năm tương ứng với bộ lọc.
     * Bỏ qua các đơn bị hủy hoặc hoàn.
     */
    public static function layDoanhThuTheoThoiGian(string $loai = 'month', ?string $giaTri = null): array {
        $res = supabase_request('GET', 'orders', [
            'select' => 'ngay_dat_hang, tong_gia_tri_don_hang, ma_trang_thai_don_hang'
        ]);
        if ($res['error'] || !is_array($res['data'])) return [];

        $tongTheoKy = [];
        $now = new DateTime();

        // Nếu không có giá trị lọc tháng thì lấy tháng hiện tại
        if ($loai === 'month' && !$giaTri) $giaTri = $now->format('Y-m');

        foreach ($res['data'] as $o) {
            if (empty($o['ngay_dat_hang'])) continue;

            try {
                $dt = new DateTime(substr($o['ngay_dat_hang'], 0, 10));
            } catch (Exception $e) {
                continue;
            }

            // Xác định key nhóm theo loại lọc
            if ($loai === 'month' && $giaTri) {
                if ($dt->format('Y-m') !== $giaTri) continue;
                $ky = $dt->format('Y-m-d'); // từng ngày trong tháng
            } elseif ($loai === 'year' && $giaTri) {
                if ($dt->format('Y') !== $giaTri) continue;
                $ky = $dt->format('Y-m');   // từng tháng trong năm
            } elseif ($loai === 'day' && $giaTri) {
                if ($dt->format('Y-m-d') !== $giaTri) continue;
                $ky = $dt->format('Y-m-d');
            } else {
                $ky = $dt->format('Y-m-d');
            }

            // Bỏ qua đơn bị hủy hoặc hoàn
            $trangThai = $o['ma_trang_thai_don_hang'] ?? 0;
            if (!in_array($trangThai, [5, 6])) {
                $giaTriDon = (float)($o['tong_gia_tri_don_hang'] ?? 0);
                $tongTheoKy[$ky] = ($tongTheoKy[$ky] ?? 0) + $giaTriDon;
            }
        }

        // Sắp xếp theo thời gian tăng dần
        ksort($tongTheoKy);

        // Chuẩn hóa đầu ra
        $ketQua = [];
        foreach ($tongTheoKy as $ky => $tien) {
            $ketQua[] = ['thoigian' => $ky, 'doanh_thu' => (int)round($tien)];
        }
        return $ketQua;
    }

    /**
     * === 3. LẤY DOANH THU THEO DANH MỤC ===
     * Tính doanh thu theo danh mục sản phẩm, bỏ qua đơn hủy/hoàn.
     */
    public static function layDoanhThuTheoDanhMuc(?string $loaiLoc = null, ?string $giaTri = null): array {
        // Gọi nhiều bảng từ Supabase
        $ordersRes   = supabase_request('GET', 'orders', ['select' => 'ma_don_hang, ngay_dat_hang, tong_gia_tri_don_hang, ma_trang_thai_don_hang']);
        $detailsRes  = supabase_request('GET', 'order_details', ['select' => 'ma_don_hang, ma_bien_the_san_pham, thanh_tien']);
        $variantsRes = supabase_request('GET', 'product_variants', ['select' => 'ma_bien_the, ma_san_pham']);
        $productsRes = supabase_request('GET', 'products', ['select' => 'ma_san_pham, ma_danh_muc']);
        $catsRes     = supabase_request('GET', 'categories', ['select' => 'ma_danh_muc, ten_danh_muc']);

        // Nếu lỗi, trả mảng rỗng
        if ($ordersRes['error'] || $detailsRes['error'] || $variantsRes['error'] || $productsRes['error'] || $catsRes['error']) {
            return [];
        }

        // Chuẩn bị map dữ liệu
        $orders     = $ordersRes['data'];
        $details    = $detailsRes['data'];
        $variantMap = array_column($variantsRes['data'], 'ma_san_pham', 'ma_bien_the');
        $productMap = array_column($productsRes['data'], 'ma_danh_muc', 'ma_san_pham');
        $catMap     = array_column($catsRes['data'], 'ten_danh_muc', 'ma_danh_muc');

        // Gom chi tiết theo mã đơn
        $byOrder = [];
        foreach ($details as $d) {
            $byOrder[$d['ma_don_hang']][] = $d;
        }

        $catTotals = [];
        foreach ($orders as $o) {
            if (!self::donHangHopLe($o, $loaiLoc, $giaTri)) continue;
            if (in_array($o['ma_trang_thai_don_hang'] ?? 0, [5, 6])) continue; // bỏ đơn hủy/hoàn

            $id = $o['ma_don_hang'];
            $rows = $byOrder[$id] ?? [];
            $totalOrder = (float)$o['tong_gia_tri_don_hang'];

            // Nếu không có chi tiết, gộp vào "Khác"
            if (empty($rows)) {
                $catTotals['Khác'] = ($catTotals['Khác'] ?? 0) + $totalOrder;
                continue;
            }

            // Tính tỉ lệ giữa tổng chi tiết và tổng đơn để phân bổ doanh thu
            $totalDetails = 0;
            foreach ($rows as $r) $totalDetails += (float)($r['thanh_tien'] ?? 0);
            if ($totalDetails <= 0) $totalDetails = $totalOrder;
            $ratio = $totalOrder / $totalDetails;

            // Phân bổ doanh thu vào từng danh mục
            foreach ($rows as $r) {
                $maSP = $variantMap[$r['ma_bien_the_san_pham']] ?? null;
                $maDM = $maSP ? ($productMap[$maSP] ?? null) : null;
                $tenDM = $maDM ? ($catMap[$maDM] ?? 'Khác') : 'Khác';
                $catTotals[$tenDM] = ($catTotals[$tenDM] ?? 0) + ((float)($r['thanh_tien'] ?? 0) * $ratio);
            }
        }

        // Sắp xếp giảm dần theo doanh thu
        arsort($catTotals);
        $out = [];
        foreach ($catTotals as $k => $v) {
            $out[] = ['ten_danh_muc' => $k, 'doanh_thu' => (int)round($v)];
        }
        return $out;
    }

    /**
     * === 4. LẤY TOP 5 SẢN PHẨM BÁN CHẠY ===
     * Tính theo số lượng bán, bỏ qua đơn hủy và hoàn, có lọc thời gian.
     */
    public static function layTop5SanPham(?string $loaiLoc = null, ?string $giaTri = null): array {
        $ct  = supabase_request('GET', 'order_details', ['select' => 'ma_don_hang, ma_bien_the_san_pham, so_luong_mua']);
        $bt  = supabase_request('GET', 'product_variants', ['select' => 'ma_bien_the, ma_san_pham, ma_mau, ma_size']);
        $sp  = supabase_request('GET', 'products', ['select' => 'ma_san_pham, ten_san_pham']);
        $mau = supabase_request('GET', 'colors', ['select' => 'ma_mau, ten_mau']);
        $size= supabase_request('GET', 'sizes', ['select' => 'ma_size, ten_size']);
        $dh  = supabase_request('GET', 'orders', ['select' => 'ma_don_hang, ma_trang_thai_don_hang, ngay_dat_hang']);

        if ($ct['error'] || $bt['error'] || $sp['error'] || $dh['error']) return [];

        // Lọc các đơn hợp lệ (không hủy/hoàn + trong phạm vi lọc)
        $validOrders = [];
        foreach ($dh['data'] as $d) {
            if (!self::donHangHopLe($d, $loaiLoc, $giaTri)) continue;
            if (!in_array($d['ma_trang_thai_don_hang'], [5, 6])) {
                $validOrders[$d['ma_don_hang']] = true;
            }
        }

        // Map dữ liệu
        $btMap   = array_column($bt['data'], null, 'ma_bien_the');
        $spMap   = array_column($sp['data'], 'ten_san_pham', 'ma_san_pham');
        $mauMap  = array_column($mau['data'], 'ten_mau', 'ma_mau');
        $sizeMap = array_column($size['data'], 'ten_size', 'ma_size');

        $count = [];
        foreach ($ct['data'] as $d) {
            if (!isset($validOrders[$d['ma_don_hang']])) continue;
            $v = $btMap[$d['ma_bien_the_san_pham']] ?? null;
            if (!$v) continue;

            // Gộp theo tên SP + màu + size
            $key = $spMap[$v['ma_san_pham']] . '|' . ($mauMap[$v['ma_mau']] ?? '-') . '|' . ($sizeMap[$v['ma_size']] ?? '-');
            $count[$key] = ($count[$key] ?? 0) + (int)($d['so_luong_mua'] ?? 0);
        }

        // Sắp xếp giảm dần và lấy top 5
        arsort($count);
        $top = array_slice($count, 0, 5, true);

        // Chuẩn hóa dữ liệu xuất ra
        $out = [];
        foreach ($top as $k => $v) {
            [$tenSP, $mau, $size] = explode('|', $k);
            $out[] = [
                'ten_san_pham' => $tenSP,
                'ten_mau'      => $mau,
                'ten_size'     => $size,
                'tong_so_luong'=> $v
            ];
        }
        return $out;
    }

    /**
     * === 5. LẤY TOP 5 SẢN PHẨM CÓ TỶ LỆ HỦY CAO ===
     */
    public static function layTop5TyLeHuy(?string $loaiLoc = null, ?string $giaTri = null): array {
        return self::layTyLeTheoTrangThai(5, $loaiLoc, $giaTri);
    }

    /**
     * === 6. LẤY TOP 5 SẢN PHẨM CÓ TỶ LỆ HOÀN CAO ===
     */
    public static function layTop5TyLeHoan(?string $loaiLoc = null, ?string $giaTri = null): array {
        return self::layTyLeTheoTrangThai(6, $loaiLoc, $giaTri);
    }

    /**
     * === HÀM CHUNG: TÍNH TỶ LỆ HỦY / HOÀN ===
     * Tính tỷ lệ đơn bị hủy hoặc hoàn trên tổng đơn chứa sản phẩm đó.
     */
    private static function layTyLeTheoTrangThai(int $trangThaiMucTieu, ?string $loaiLoc, ?string $giaTri): array {
        $orders   = supabase_request('GET', 'orders', ['select' => 'ma_don_hang, ngay_dat_hang, ma_trang_thai_don_hang']);
        $details  = supabase_request('GET', 'order_details', ['select' => 'ma_don_hang, ma_bien_the_san_pham, so_luong_mua']);
        $variants = supabase_request('GET', 'product_variants', ['select' => 'ma_bien_the, ma_san_pham']);
        $products = supabase_request('GET', 'products', ['select' => 'ma_san_pham, ten_san_pham']);

        if ($orders['error'] || $details['error'] || $variants['error'] || $products['error']) return [];

        // Lọc đơn theo thời gian
        $orderData = array_filter($orders['data'], fn($o) => self::donHangHopLe($o, $loaiLoc, $giaTri));

        // Chuẩn bị map dữ liệu
        $variantMap = array_column($variants['data'], 'ma_san_pham', 'ma_bien_the');
        $productMap = array_column($products['data'], 'ten_san_pham', 'ma_san_pham');

        // Tổng đơn và đơn bị hủy/hoàn theo sản phẩm
        $totalCount = [];
        $targetCount = [];

        foreach ($details['data'] as $d) {
            $order = array_values(array_filter($orderData, fn($o) => $o['ma_don_hang'] == $d['ma_don_hang']));
            if (empty($order)) continue;
            $order = $order[0];

            $maSP = $variantMap[$d['ma_bien_the_san_pham']] ?? null;
            if (!$maSP) continue;

            $tenSP = $productMap[$maSP] ?? 'Không rõ';
            $totalCount[$tenSP] = ($totalCount[$tenSP] ?? 0) + ($d['so_luong_mua'] ?? 0);

            // Nếu đơn thuộc trạng thái hủy hoặc hoàn
            if (($order['ma_trang_thai_don_hang'] ?? 0) == $trangThaiMucTieu) {
                $targetCount[$tenSP] = ($targetCount[$tenSP] ?? 0) + ($d['so_luong_mua'] ?? 0);
            }
        }

        // Tính tỷ lệ phần trăm
        $tyle = [];
        foreach ($totalCount as $sp => $tong) {
            $tg = $targetCount[$sp] ?? 0;
            $tyle[$sp] = $tong > 0 ? round($tg / $tong * 100, 2) : 0;
        }

        // Sắp xếp và lấy top 5
        arsort($tyle);
        $top = array_slice($tyle, 0, 5, true);

        // Chuẩn hóa đầu ra
        $out = [];
        foreach ($top as $sp => $pct) {
            $out[] = ['ten_san_pham' => $sp, 'ty_le' => $pct];
        }
        return $out;
    }
}
?>
