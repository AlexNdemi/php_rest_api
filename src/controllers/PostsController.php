<?php
namespace src\controllers;
use src\interfaces;
use src\core\JsonResponse;
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

  public function updatePost(string $id,string $category_id,string $title,string $body,string $author){
    $cleaned = [
         'id'=> (int) $this->clean($id),
        'category_id' => $this->clean($category_id),
        'title' => $this->clean($title),
        'body' => $this->clean($body),
        'author' => $this->clean($author)
    ];
    $this->model->update(
        id: $cleaned['id'],
        category_id: $cleaned['category_id'],
        title: $cleaned['title'],
        body: $cleaned['body'],
        author: $cleaned['author']
    );
  }

  public function delete (string $id): void{ 
    $idOfPostToDelete = (int) $id;
    $this->model->Delete(id: $idOfPostToDelete);
  }
  
}
