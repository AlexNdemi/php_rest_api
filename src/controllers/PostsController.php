<?php
namespace src\controllers;
use src\interfaces;
use src\interfaces\PostControllerContract;
Class PostsController {
   private $model;
  public function __construct(interfaces\PostsModelContract $model){
      $this->model = $model;
  }

  private function clean(string $value): string {
    return htmlspecialchars(strip_tags($value));
  }
  public function createPost(string $category_id, string $title, string $body, string $author): void {
    $cleaned = [
        'category_id' => $this->clean($category_id),
        'title' => $this->clean($title),
        'body' => $this->clean($body),
        'author' => $this->clean($author)
    ];

    $this->model->create(
        $cleaned['category_id'],
        $cleaned['title'],
        $cleaned['body'],
        $cleaned['author']
    );
  } 
  
}
