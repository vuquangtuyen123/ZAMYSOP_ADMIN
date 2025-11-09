<?php
require_once __DIR__ . '/../config/supabase.php';

class CollectionModel {

	public function getAll($search = '', $limit = 8, $page = 1) {
		$offset = ($page - 1) * $limit;
        $params = [
            'select' => '*,collection_images(ma_hinh_anh,duong_dan_anh)',
            'order' => 'ma_bo_suu_tap.desc',
			'limit' => $limit,
			'offset' => $offset
		];
		if ($search !== '') {
            $params['or'] = '(ten_bo_suu_tap.ilike.*' . $search . '*,mo_ta.ilike.*' . $search . '*)';
		}
        $res = supabase_request('GET', 'collections', $params);
		return $res['error'] ? [] : $res['data'];
	}

	public function countAll($search = '') {
        $params = ['select' => 'ma_bo_suu_tap'];
        if ($search !== '') {
            $params['or'] = '(ten_bo_suu_tap.ilike.*' . $search . '*,mo_ta.ilike.*' . $search . '*)';
        }
        $res = supabase_request('GET', 'collections', $params);
		return $res['error'] ? 0 : count($res['data']);
	}

    public function getById($ma_bo_suu_tap) {
        $res = supabase_request('GET', 'collections', [
            'select' => '*,collection_images(ma_hinh_anh,duong_dan_anh)',
            'ma_bo_suu_tap' => 'eq.' . $ma_bo_suu_tap
        ]);
		return $res['error'] ? [] : ($res['data'][0] ?? []);
	}

    // Thêm ảnh vào collection
    public function addImage($ma_bo_suu_tap, $duong_dan_anh) {
        $body = [
            'ma_bo_suu_tap' => $ma_bo_suu_tap,
            'duong_dan_anh' => $duong_dan_anh
        ];
        $res = supabase_request('POST', 'collection_images', [], $body);
        return !$res['error'];
    }

    // Xóa ảnh khỏi collection
    public function deleteImage($ma_hinh_anh) {
        $res = supabase_request('DELETE', 'collection_images', ['ma_hinh_anh' => 'eq.' . $ma_hinh_anh]);
        return !$res['error'];
    }

    // Lấy tất cả ảnh của một collection
    public function getImages($ma_bo_suu_tap) {
        $res = supabase_request('GET', 'collection_images', [
            'select' => '*',
            'ma_bo_suu_tap' => 'eq.' . $ma_bo_suu_tap,
            'order' => 'ma_hinh_anh.asc'
        ]);
        return $res['error'] ? [] : $res['data'];
    }

    public function create($data) {
        $body = [
            'ten_bo_suu_tap' => $data['ten_bo_suu_tap'],
            'mo_ta' => $data['mo_ta'] ?? null,
            'trang_thai' => isset($data['trang_thai']) ? (bool)$data['trang_thai'] : true,
        ];
        $res = supabase_request('POST', 'collections', [], $body);
        if ($res['error']) {
            return false;
        }
        
        // Lấy ma_bo_suu_tap vừa tạo (POST với return=representation sẽ trả về data)
        $ma_bo_suu_tap = null;
        if (isset($res['data']['ma_bo_suu_tap'])) {
            $ma_bo_suu_tap = $res['data']['ma_bo_suu_tap'];
        } elseif (isset($res['data'][0]['ma_bo_suu_tap'])) {
            $ma_bo_suu_tap = $res['data'][0]['ma_bo_suu_tap'];
        }
        
        if ($ma_bo_suu_tap && !empty($data['images'])) {
            // Thêm các ảnh vào collection_images
            foreach ($data['images'] as $imageUrl) {
                if (!empty($imageUrl)) {
                    $this->addImage($ma_bo_suu_tap, $imageUrl);
                }
            }
        }
        
        // Trả về ma_bo_suu_tap để controller sử dụng
        return $ma_bo_suu_tap;
	}

    public function update($ma_bo_suu_tap, $data) {
		$update = [];
        if (isset($data['ten_bo_suu_tap'])) $update['ten_bo_suu_tap'] = $data['ten_bo_suu_tap'];
        if (isset($data['mo_ta'])) $update['mo_ta'] = $data['mo_ta'];
		if (isset($data['trang_thai'])) $update['trang_thai'] = (bool)$data['trang_thai'];
        $res = supabase_request('PATCH', 'collections', ['ma_bo_suu_tap' => 'eq.' . $ma_bo_suu_tap], $update);
        
        // Thêm ảnh mới nếu có
        if (!$res['error'] && !empty($data['images'])) {
            foreach ($data['images'] as $imageUrl) {
                if (!empty($imageUrl)) {
                    $this->addImage($ma_bo_suu_tap, $imageUrl);
                }
            }
        }
        
		return !$res['error'];
	}

    public function delete($ma_bo_suu_tap) {
        $res = supabase_request('DELETE', 'collections', ['ma_bo_suu_tap' => 'eq.' . $ma_bo_suu_tap]);
		return !$res['error'];
	}
}

?>

