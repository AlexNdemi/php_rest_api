<?php
namespace src\attributes;
use Attribute;
#[Attribute]
class Post extends Route {
  public function __construct(public string $route){
    parent::__construct(route:$route,requestMethod: "post");
  }
 
}