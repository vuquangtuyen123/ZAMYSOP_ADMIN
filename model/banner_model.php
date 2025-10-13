<?php
require_once __DIR__ . '/../config/supabase.php';

class BannerModel {
    public function getAllBanners() {
        $result = supabase_request('GET', 'banners', []);
        return $result['error'] ? [] : $result['data'];
    }

    public function createBanner($data) {
        $result = supabase_request('POST', 'banners', [], $data);
        return !$result['error'];
    }

    public function deleteBanner($ma_banner) {
    $result = supabase_request('DELETE', 'banners', ['ma_banner' => 'eq.' . $ma_banner]);
    return !$result['error'];
}
public function updateBannerStatus($ma_banner, $trang_thai) {
    $data = ['trang_thai' => $trang_thai];
    $result = supabase_request('PATCH', 'banners', ['ma_banner' => 'eq.' . $ma_banner], $data);
    return !$result['error'];
}
}