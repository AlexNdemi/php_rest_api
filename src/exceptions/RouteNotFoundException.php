<?php
namespace src\exceptions;;
class RouteNotFoundException extends \Exception{
  protected $message = 'Route not found';
}