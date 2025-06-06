<?php
namespace src\core;

class JsonResponse
{   public static function
    sendCorsHeaders(array $allowedMethods = ['GET']):void {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");

        // Join allowed methods (['GET', 'POST']) => 'GET, POST'
        header("Access-Control-Allow-Methods: " . implode(', ', $allowedMethods));
    }
    public static function success(array $data = [], int $code = 200,array $allowedMethods = ['GET']): void
    {
        self::sendCorsHeaders($allowedMethods);    
        http_response_code($code);
        self::send($data);
        exit;
    }

    public static function error(string $message, int $code = 400, array $extra = []): void
    {   self::sendCorsHeaders();
        http_response_code($code);
        self::send(['success' => false, 'error' => $message, "extra"=>$extra]);
        exit;
    }

    public static function send(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public static function noContent(): void
    {
        http_response_code(204);
    }
}
