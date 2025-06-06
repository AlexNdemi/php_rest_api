<?php
namespace src\core;
use src\core\Container;
use src\core\Router\Router;
Class App{
  public function __construct(private Router $router,private Container $container,protected array $request = []){ 
  }

  public function run(){
    $queryParams = $this->request["method"] === "GET" ? $_GET:[];
    $this->router?->resolve(requestUri: $this->request["uri"],requestMethod: strtolower($this->request["method"]),
    queryParams: $queryParams
  );
  }
}