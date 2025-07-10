<?php
declare(strict_types=1);
namespace src\core\Router;
use src\exceptions;
use src\attributes;
use src\core\Container;

class Router{
  private array $routes=[];

  public function __construct(private Container $container){

  }

  public function register(string $method,string $route,array|callable $action ){
    $this->routes[$method][$route] = $action;
    return $this;
  }


  public function get(string $route,array|callable $action){
    return $this->register('get',$route,$action);
  }


  public function routes(){
    return $this->routes;
  }


  public function post(string $route,array|callable $action){
    return $this->register('post',$route,$action);
  }


  public function registerRoutesFromMethodAttributes(array $controllers){
    foreach($controllers as $controller){
      $controllerClass =new \ReflectionClass($controller);
      $controllerAttributes = $controllerClass->getAttributes(attributes\RouteConstructor::class);
      $controllerArguments=[];
      if(!empty($controllerAttributes)){
        foreach($controllerAttributes as $controllerAttribute){
          $instance = $controllerAttribute->newInstance();
          $classArgs = $instance->classArgs;
          foreach($classArgs as $classArg){
            $controllerArguments[]= $classArg;
          }
        }
      }
      
      foreach($controllerClass->getMethods() as $controllerMethod){
        $methodAttributes = $controllerMethod->getAttributes(attributes\Route::class,\ReflectionAttribute::IS_INSTANCEOF);
        foreach($methodAttributes as $methodAttribute){
          $route= $methodAttribute->newInstance();
          $methodArgs=is_array($route->methodArgs)? $route->methodArgs:[$route->methodArgs];
          
          $this->register(route:$route->route,method:$route->requestMethod,action:[$controller,$controllerMethod->getName(),[...$controllerArguments],[...$methodArgs]]);
        };
      }
    } ;

  }

  
  public function resolve(string $requestUri,string $requestMethod,array $queryParams = []){
    $route = \explode('?',$requestUri)[0];
    $action = null;
    $params=[];
    foreach($this->routes[$requestMethod] as $routePattern=>$routeAction){
      $pattern= preg_replace("#\{[\w]+\}#",'([^/]+)',$routePattern);
      $pattern = "#^$pattern$#";
      if(preg_match($pattern,$route,$matches)){
        array_shift($matches);
        $action = $routeAction;
        $params = array_map('rawurldecode', $matches);
        break;
      }

    }
    if(!$action){
      throw new \src\exceptions\RouteNotFoundException();
    }

    if(!is_callable($action) && !is_array($action)){
      throw new exceptions\IsNotCallableException();
    }
    if(is_callable($action)){
      call_user_func($action);
      return;
    }
    if(!is_array($action)){
      throw new exceptions\IsNotArrayException();
    }
    [$class,$method,$ClassConstructorArgs,$methodArgs]=$action;
    

    $this->invokeClassMethod(class: $class,method: $method, constructorArgs: $ClassConstructorArgs,methodArgs: $methodArgs,params:$params,queryParams:$queryParams);
  }

  private function invokeClassMethod(string $class, string $method, array $constructorArgs, array $methodArgs,$params,$queryParams){
     if (!class_exists($class)) {
       throw new exceptions\ClassNotFoundException("Class {$class} not found.");
     }

     $this->container->set($constructorArgs[0][0],function(container $container)use($constructorArgs){
      return new $constructorArgs[0][0]($container->get($constructorArgs[0][1][0])
      );
     });

     $instance = $this->container->get($class);

    if (!method_exists($instance, $method)) {
         throw new exceptions\MethodNotFoundException("Method {$method} not found in class {$class}.");
    }

    $result = call_user_func_array([$instance, $method], [...$params,...$methodArgs,$queryParams]);

    if (is_array($result) || is_object($result)) {
    echo json_encode($result);
    } elseif (is_string($result)) { // plain text, if needed
    } 
     
  }

   }
