<?php
require_once __DIR__ . '/../config/supabase.php';

class LoginModel {
    public function checkLogin($email, $password) {
        $credentials = [
            'email' => $email,
            'password' => $password
        ];

        $response = supabase_request("POST", "auth/v1/token?grant_type=password", [], $credentials);

        if (!$response['error'] && isset($response['data']['access_token'])) {
            $userResponse = supabase_request("GET", "users", [
                "email" => "eq.$email",
                "select" => "*"
            ]);

            if (!$userResponse['error'] && !empty($userResponse['data'])) {
                return $userResponse['data'][0];
            }
        }
        return false;
    }
}
