<?php
namespace src\services;
use config\Config;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv;
final class TokenService{
   private static ?TokenService $instance = null;
   private  static string $secret;
   private const ACCESS_EXP = 300; // 5 min
   private const REFRESH_EXP = 1209600; // 14 days
    private function __construct ()  {
        $envPath = __DIR__ ."/../../../";
        $dotenv = Dotenv\Dotenv::createImmutable($envPath);
        $dotenv->load();

        $cfg = new Config($_ENV);
        self::$secret = $cfg->JWT_SECRET;
    }
    private static function getInstance(): TokenService|null{
         if(self::$instance === null){
            self::$instance = new TokenService();
         }
         return self::$instance;
    }

   


    public static function generateAccessToken(array $user): string
    {
        $instance = self::getInstance();
        $payload = [
            'sub' => $user['id'],
            'email' => $user['email'],
            'exp' => time() + self::ACCESS_EXP
        ];
        return JWT::encode($payload, $instance::$secret, 'HS256');
    }

    public static function generateRefreshToken(array $user): string
    {
        $instance = self::getInstance();
        $payload = [
            'sub' => $user['id'],
            'email' => $user['email'],
            'type' => 'refresh',
            'exp' => time() + $instance::REFRESH_EXP
        ];
        return JWT::encode($payload, self::$secret, 'HS256');
    }

    public static function verify(string $token)
    {
        self::getInstance();
        return JWT::decode($token, new Key(self::$secret, 'HS256'));
    }
}