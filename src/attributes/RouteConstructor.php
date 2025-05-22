<?php
namespace src\attributes;
use Attribute;
#[Attribute]
Class RouteConstructor {
  public function __construct(public array $classArgs=[]){}
} 