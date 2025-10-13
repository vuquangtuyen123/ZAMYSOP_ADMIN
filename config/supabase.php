<?php
/**
 * Tệp cấu hình kết nối Supabase
 * 
 * Tệp này chứa thông tin cấu hình và hàm kết nối đến cơ sở dữ liệu Supabase.
 * Supabase là một backend-as-a-service mã nguồn mở, cung cấp:
 * - Cơ sở dữ liệu PostgreSQL
 * - Xác thực người dùng
 * - API RESTful tự động
 * - Lưu trữ file
 * 
 * @author Đội phát triển
 * @version 1.0
 */

// Cấu hình Supabase - Thông tin kết nối cơ sở dữ liệu
$SUPABASE_URL = 'https://acddbjalchiruigappqg.supabase.co/rest/v1'; // URL API của Supabase project
$SUPABASE_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImFjZGRiamFsY2hpcnVpZ2FwcHFnIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTkwMzAzMTQsImV4cCI6MjA3NDYwNjMxNH0.Psefs-9-zIwe8OjhjQOpA19MddU3T9YMcfFtMcYQQS4'; // API Key để xác thực
$SUPABASE_STORAGE_URL = 'https://acddbjalchiruigappqg.supabase.co/storage/v1'; // URL Storage API của Supabase

/**
 * Hàm gửi yêu cầu HTTP đến Supabase API
 * 
 * Hàm này được sử dụng để thực hiện các thao tác CRUD (Create, Read, Update, Delete)
 * với cơ sở dữ liệu thông qua Supabase REST API.
 * 
 * @param string $method Phương thức HTTP (GET, POST, PUT, DELETE, PATCH)
 * @param string $endpoint Đường dẫn API endpoint (ví dụ: 'users', 'products')
 * @param array $params Tham số query string (ví dụ: filter, select, order)
 * @param array|null $body Dữ liệu gửi kèm request (cho POST, PUT, PATCH)
 * 
 * @return array Mảng chứa kết quả:
 *               - 'error': true/false - Có lỗi hay không
 *               - 'status': HTTP status code (200, 404, 500,...)
 *               - 'data': Dữ liệu trả về từ API
 *               - 'message': Thông báo lỗi (nếu có)
 */
function supabase_request($method, $endpoint, $params = [], $body = null) {
    global $SUPABASE_URL, $SUPABASE_KEY;

    $url = $SUPABASE_URL . '/' . ltrim($endpoint, '/');
    if ($params) $url .= '?' . http_build_query($params);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $SUPABASE_KEY",
        "Authorization: Bearer $SUPABASE_KEY",
        "Content-Type: application/json"
    ]);

    if ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        if ($body) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $response = curl_exec($ch);
    $err = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err) {
        return ['error' => true, 'message' => $err, 'data' => []];
    }

    $decoded_response = json_decode($response, true);
    if ($http_code >= 400) {
        return [
            'error' => true,
            'message' => 'HTTP Error: ' . $http_code,
            'data' => $decoded_response ?: []
        ];
    }

    return [
        'error' => false,
        'status' => $http_code,
        'data' => $decoded_response ?: []
    ];
}

/**
 * Hàm upload file lên Supabase Storage
 * 
 * Hàm này thực hiện upload file lên bucket Storage của Supabase.
 * 
 * @param string $bucket Tên bucket lưu trữ (ví dụ: 'news-images')
 * @param string $fileName Tên file trên Storage
 * @param string $filePath Đường dẫn cục bộ đến file cần upload
 * 
 * @return array Mảng chứa kết quả:
 *               - 'error': true/false - Có lỗi hay không
 *               - 'data': Đường dẫn file trên Storage nếu thành công
 *               - 'message': Thông báo lỗi (nếu có)
 */
function supabase_storage_upload($bucket, $fileName, $filePath) {
    global $SUPABASE_STORAGE_URL, $SUPABASE_KEY;

    // Mã hóa toàn bộ đường dẫn để tránh ký tự không hợp lệ
    $encodedPath = rawurlencode($bucket) . '/' . rawurlencode($fileName);

    // Xây dựng URL đầy đủ
    $url = $SUPABASE_STORAGE_URL . '/object/' . $encodedPath;

    $fileContent = file_get_contents($filePath);
    if ($fileContent === false) {
        return ['error' => true, 'message' => 'Không thể đọc file', 'data' => null];
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $SUPABASE_KEY",
        "Content-Type: application/octet-stream",
        "x-upsert: true" // Cho phép ghi đè nếu file đã tồn tại
    ]);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err) {
        return ['error' => true, 'message' => $err, 'data' => null];
    }

    if ($http_code >= 400) {
        $decoded_response = json_decode($response, true);
        return ['error' => true, 'message' => 'HTTP Error: ' . $http_code . ' - ' . ($decoded_response['message'] ?? ''), 'data' => $decoded_response ?: null];
    }

    // Trả về đường dẫn file
    return ['error' => false, 'data' => ['path' => $bucket . '/' . $fileName], 'message' => ''];
}