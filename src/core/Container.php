<?php
namespace src\core;
use src\exceptions;
use Psr\Container\ContainerInterface;

Class Container implements ContainerInterface{

  public array $entries=[];
  public function get(string $id){
    if(!$this->has($id)){
      return $this->resolve($id);
    }
    $entry = $this->entries[$id];

    if(is_callable($entry)) {
      return $entry($this);
    }
    if(is_string($entry)){
      return $this->resolve($entry);
    }
    
  }
  public function has(string $id): bool{
    return isset($this->entries[$id]);
  }
  public function set(string $id,callable|string $concrete):void{
    $this->entries[$id]=$concrete;
  }

  public function resolve(string $id){
    $reflectionClass= new \ReflectionClass($id);
    if(!$reflectionClass->isInstantiable()){
      throw new exceptions\ContainerException("class {$id} cannot be initialized");
    }

    $constructor=$reflectionClass->getConstructor();
    if(!$constructor){
      return new $id;
    }
    $parameters=$constructor->getParameters();
    if(!$parameters){
      return new $id;
    }
    $dependencies = array_map(function(\ReflectionParameter $parameter){
      $name = $parameter->getName();
      $type = $parameter->getType();
      if(!$type){
        throw new exceptions\ContainerException("failed to resolve class {$name} because it does not have a type hint"); 
      }
      if($type instanceOf \ReflectionUnionType){
        throw new exceptions\ContainerException("Failed to resolve parameter '{$name}' due to unsupported union type dependencies"); 
      }
      $isOfReflectionNamedTypeButNotBuiltIn= $type instanceof \ReflectionNamedType && !$type->isBuiltin();
      if(!$isOfReflectionNamedTypeButNotBuiltIn){
        throw new exceptions\ContainerException("failed to resolve class {$name}  because {$type}  is of built-in type"); 
      }
      return $this->get($type->getName());
    },$parameters);
   return $reflectionClass->newInstanceArgs($dependencies);
  }
}