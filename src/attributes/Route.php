<?php
namespace src\attributes;
use Attribute;
#[Attribute]
class Route{
  public function __construct(public string $route,public array $methodArgs = [],public string $requestMethod = "get"){
    
  }
}
