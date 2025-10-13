<?php
require_once __DIR__ . '/../config/supabase.php';

class NewsModel {

public function getAllNews($conditions = [], $search = '', $limit = 0, $offset = 0) {
    $queryParams = [
        'select' => '*',
        'order' => 'ma_tin_tuc.desc'
    ];

    if (!empty($conditions)) {
        foreach ($conditions as $key => $value) {
            $queryParams[$key] = $value;
        }
    }

    // Tìm kiếm theo tiêu đề (chữ hoặc số đều được)
    if ($search) {
        $queryParams['tieu_de'] = 'ilike.*' . $search . '*';
    }

    if ($limit > 0) $queryParams['limit'] = $limit;
    if ($offset > 0) $queryParams['offset'] = $offset;

    $result = supabase_request('GET', 'news', $queryParams);
    return $result['error'] ? [] : $result['data'];
}

public function getTotalNews($conditions = [], $search = '') {
    $queryParams = ['select' => 'ma_tin_tuc'];

    if (!empty($conditions)) {
        foreach ($conditions as $key => $value) {
            $queryParams[$key] = $value;
        }
    }

    if ($search) {
        $queryParams['tieu_de'] = 'ilike.*' . $search . '*';
    }

    $result = supabase_request('GET', 'news', $queryParams);
    return $result['error'] ? 0 : count($result['data']);
}
    public function getNewsById($ma_tin_tuc) {
        $result = supabase_request('GET', 'news', ['select' => '*', 'ma_tin_tuc' => 'eq.' . $ma_tin_tuc]);
        return $result['error'] ? [] : ($result['data'][0] ?? []);
    }

    public function createNews($data) {
        $result = supabase_request('POST', 'news', [], [
            'tieu_de' => $data['tieu_de'],
            'noi_dung' => $data['noi_dung'],
            'hinh_anh' => $data['hinh_anh'] ?? null,
            'ngay_dang' => date('Y-m-d H:i:s'),
            'trang_thai_hien_thi' => $data['trang_thai_hien_thi']
        ]);
        return !$result['error'];
    }

    public function updateNews($data) {
        $result = supabase_request('PATCH', 'news', ['ma_tin_tuc' => 'eq.' . $data['ma_tin_tuc']], [
            'tieu_de' => $data['tieu_de'],
            'noi_dung' => $data['noi_dung'],
            'hinh_anh' => $data['hinh_anh'] ?? null,
            'ngay_dang' => date('Y-m-d H:i:s'),
            'trang_thai_hien_thi' => $data['trang_thai_hien_thi']
        ]);
        return !$result['error'];
    }

    public function deleteNews($ma_tin_tuc) {
        $result = supabase_request('DELETE', 'news', ['ma_tin_tuc' => 'eq.' . $ma_tin_tuc]);
        return !$result['error'];
    }
    public function updateStatus($ma_tin_tuc, $trang_thai_hien_thi) {
    $result = supabase_request('PATCH', 'news', ['ma_tin_tuc' => 'eq.' . $ma_tin_tuc], [
        'trang_thai_hien_thi' => $trang_thai_hien_thi
    ]);
    return !$result['error'];
}
   
}