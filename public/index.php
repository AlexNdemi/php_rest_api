<?php
declare(strict_types=1);
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS,PUT,DELETE");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Content-Length: 0");
    header("Content-Type: application/json; charset=UTF-8");
    exit;
}

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
require __DIR__. '/../vendor/autoload.php';
use src\core\Container;
use src\interfaces;
use src\models\Pdo;
use src\core\Router;
use src\api;
use src\core\JsonResponse;

$container= new Container();
$container->set(id: interfaces\PostsModelContract::class,concrete: Pdo\PdoPostsModel::class);


$router = new Router\Router($container);
$router -> registerRoutesFromMethodAttributes(controllers: [
  api\ReadRoute::class,
  api\CreateRoute::class,
  api\UpdateRoute::class,
  api\DeleteRoute::class
]);
try{
(new \src\core\App(container:$container,router:$router,request: ["uri"=>$_SERVER['REQUEST_URI'],"method"=>$_SERVER['REQUEST_METHOD']]))->run();
}catch (Throwable $e) {
    JsonResponse::error(
        message: "Server error: " . $e->getMessage(),
        code: 500,
        extra: [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    );
}

/*echo '<pre>';
  print_r($_SERVER);
echo '</pre>';*/




