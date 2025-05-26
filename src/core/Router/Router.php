<?php
declare(strict_types=1);
namespace src\core\Router;
use src\exceptions;
use src\attributes;

class Router{
  private array $routes=[];

  public function __construct(private \src\core\Container $container){

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
  public function resolve(string $requestUri,string $requestMethod){
    $route = \explode('?',$requestUri)[0];
    // $action = $this->routes[$requestMethod][$route] ?? null;
    $action = null;
    $params=[];
    foreach($this->routes[$requestMethod] as $routePattern=>$routeAction){
      $pattern= preg_replace("#\{[\w]+\}#",'([\w-]+)',$routePattern);
      $pattern = "#^".$pattern."$#";
      if(preg_match($pattern,$route,$matches)){
        array_shift($matches);
        $action = $routeAction;
        $params = $matches;
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

    $this->invokeClassMethod(class: $class,method: $method, constructorArgs: $ClassConstructorArgs,methodArgs: $methodArgs,params:$params);
  }

  private function invokeClassMethod(string $class, string $method, array $constructorArgs, array $methodArgs,$params): void {
    if (!class_exists($class)) {
        throw new exceptions\ClassNotFoundException("Class {$class} not found.");
    }

    foreach($constructorArgs as $constructorArg){
      $argsForClasses=[];
      $argsForNonClasses=[];
      if(gettype($constructorArg) === 'array' && is_string($constructorArg[0]) && class_exists($constructorArg[0])){
        $this->parseClassArg ($constructorArg);
        $argsForClasses[]=$this->container->get($constructorArg[0]);  
      }else{
         $argsForNonClasses[]=$constructorArg;
      }
      $this->container->set($class,fn()
        => new $class(...$argsForClasses,...$argsForNonClasses)
      );

      $instance = $this->container->get($class);
      echo "" ;

    }

    if (!method_exists($instance, $method)) {
        throw new exceptions\MethodNotFoundException("Method {$method} not found in class {$class}.");
    }

    $result = call_user_func_array([$instance, $method], [...$params,...$methodArgs]);

    if (is_array($result) || is_object($result)) {
    echo json_encode($result);
    } elseif (is_string($result)) {
    echo $result; // plain text, if needed
    }
}
  public function parseClassArg( array $classArg){                           
    forEach($classArg as $arrayItem){

        if(is_string($arrayItem[0]) && class_exists($arrayItem[0])){
          $this->container->set($arrayItem[0],function($arrayItem){

            $args= $this->parseArrayItemsIntoClassesAndNonclasses($arrayItem[1]);
            $arrayItemClasses = [];
            [$classArgs, $otherArgs] = $args;
            foreach($classArgs as $classArg){
             $arrayItemClasses = $this->container->get($classArg);
            }
            return new $arrayItem[0](...$arrayItemClasses,...$otherArgs);
          }) ; 
        

        }
    }
    return ;
  }
  // public function registerClassArgItemIntoContainer(array $classArg){
  //     $args = $this->parseClassArg($classArg);
  //           [$classArgs, $otherArgs] = $args;
  //           $classArgsInstances = [];
  //           foreach ($classArgs as $classArg){
  //               $classArgsInstances[]=$this->container->get($classArg);
  //           }
  //           $this->container->set($classArg[0],fn()=> new $classArg[0](...$classArgsInstances,...$otherArgs));
  // }
  private function parseArrayItemsIntoClassesAndNonclasses($array):array{
      $itemsThatAreClasses=[];
      $other=[];
      foreach($array as $item){
          if(is_string($item) && class_exists($item)){
            $itemsThatAreClasses[]=$item;  
          }else{
            $other[]=$item;
          }
      }
    return [...$itemsThatAreClasses,...$other];
      
  }

  }
