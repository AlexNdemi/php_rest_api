<?php
namespace src\core;

class JsonResponse
{
    public static function success(array $data = [], int $code = 200): void
    {
        http_response_code($code);
        self::send([ ...$data]);
    }

    public static function error(string $message, int $code = 400, array $extra = []): void
    {
        http_response_code($code);
        self::send(['success' => false, 'error' => $message, ...$extra]);
    }

    public static function send(array $data): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public static function noContent(): void
    {
        http_response_code(204);
    }
}
