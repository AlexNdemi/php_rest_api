<?php
namespace src\core;

use src\services\TokenService;

class AuthMiddleware
{
    public static function auth()
    {
        $headers = getallheaders();
        $auth = $headers['Authorization'] ?? '';
        if (!preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
            JsonResponse::error('Missing token', 401);
            exit;
        }

        try {
            $decoded = TokenService::verify($matches[1]);
            return $decoded;
        } catch (\Throwable $e) {
            JsonResponse::error("Invalid access token", 401);
            exit;
        }
    }
}
