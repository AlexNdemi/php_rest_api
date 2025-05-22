<?php
namespace src\controllers;
use src\interfaces;
use src\interfaces\PostControllerContract;
Class PostsControllers {
   private $model;
  public function __construct($model){
      $this->model = $model;
  }

  
}
