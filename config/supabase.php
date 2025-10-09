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
 * 
 * @example
 * // Lấy tất cả users
 * $result = supabase_request('GET', 'users', ['select' => '*']);
 * 
 * // Lấy user theo email
 * $result = supabase_request('GET', 'users', ['email' => 'eq.admin@gmail.com']);
 * 
 * // Tạo user mới
 * $result = supabase_request('POST', 'users', [], ['name' => 'John', 'email' => 'john@example.com']);
 */
function supabase_request($method, $endpoint, $params = [], $body = null) {
    // Khai báo sử dụng biến global
    global $SUPABASE_URL, $SUPABASE_KEY;

    // Tạo URL hoàn chỉnh cho API request
    $url = $SUPABASE_URL . '/' . ltrim($endpoint, '/'); // Loại bỏ dấu '/' đầu endpoint nếu có
    if ($params) $url .= '?' . http_build_query($params); // Thêm query parameters nếu có

    // Khởi tạo CURL để gửi HTTP request
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Trả về response thay vì in ra màn hình
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $SUPABASE_KEY",                    // API key trong header
        "Authorization: Bearer $SUPABASE_KEY",      // Token xác thực
        "Content-Type: application/json"            // Định dạng dữ liệu gửi/nhận
    ]);

    // Thiết lập HTTP method và request body
    if ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method)); // Thiết lập method (POST, PUT, DELETE,...)
        if ($body) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body)); // Chuyển body thành JSON
    }

    // Thực thi request và lấy thông tin phản hồi
    $response = curl_exec($ch);                            // Thực thi request
    $err = curl_error($ch);                               // Lấy lỗi CURL (nếu có)
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   // Lấy HTTP status code
    curl_close($ch);                                      // Đóng kết nối CURL

    // Xử lý và trả về kết quả
    if ($err) {
        // Trường hợp có lỗi kết nối
        return ['error' => true, 'message' => $err, 'data' => []];
    }

    // Chuyển đổi JSON response thành mảng PHP
    $decoded_response = json_decode($response, true);
    
    // Kiểm tra HTTP status code để xác định lỗi
    if ($http_code >= 400) {
        return [
            'error' => true, 
            'message' => 'HTTP Error: ' . $http_code,
            'data' => $decoded_response ?: []
        ];
    }

    // Trả về kết quả thành công
    return [
        'error' => false, 
        'status' => $http_code, 
        'data' => $decoded_response ?: []
    ];
}