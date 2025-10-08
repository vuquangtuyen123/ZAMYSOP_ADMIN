<?php
// Cấu hình Supabase
$SUPABASE_URL = 'https://acddbjalchiruigappqg.supabase.co/rest/v1';
$SUPABASE_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImFjZGRiamFsY2hpcnVpZ2FwcHFnIiwicm9sZCI6ImFub24iLCJpYXQiOjE3NTkwMzAzMTQsImV4cCI6MjA3NDYwNjMxNH0.Psefs-9-zIwe8OjhjQOpA19MddU3T9YMcfFtMcYQQS4';

// Hàm gọi Supabase API
function supabase_request($method, $endpoint, $params = [], $body = null) {
    global $SUPABASE_URL, $SUPABASE_KEY;

    // Tạo URL
    $url = $SUPABASE_URL . '/' . ltrim($endpoint, '/');
    if ($params) $url .= '?' . http_build_query($params);

    // Khởi tạo CURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $SUPABASE_KEY",
        "Authorization: Bearer $SUPABASE_KEY",
        "Content-Type: application/json"
    ]);

    // Thiết lập method và body
    if ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        if ($body) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    // Thực thi và xử lý
    $response = curl_exec($ch);
    $err = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $err 
        ? ['error' => true, 'message' => $err]
        : ['error' => false, 'status' => $http_code, 'data' => json_decode($response, true)];
}