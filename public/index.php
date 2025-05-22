<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
require __DIR__. '/../vendor/autoload.php';
use src\core\Container;
use src\interfaces;
use src\models\Pdo;
use src\core\Router;
use src\api;

$container= new Container();
$container->set(id: interfaces\PostsModelContract::class,concrete: Pdo\PdoPostsModel::class);

$router = new Router\Router($container);
$router -> registerRoutesFromMethodAttributes(controllers: [
  api\ReadRoute::class
]);

(new \src\core\App(container:$container,router:$router,request: ["uri"=>$_SERVER['REQUEST_URI'],"method"=>$_SERVER['REQUEST_METHOD']]))->run(); 

/*echo '<pre>';
  print_r($_SERVER);
echo '</pre>';*/




