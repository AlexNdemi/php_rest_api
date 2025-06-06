<?php
namespace src\views;
use src\interfaces;
Class PostsView {
  private $model;
  public function __construct( interfaces\PostsModelContract $model){
    $this->model = $model;
  }
  public function getAllPosts(){
    $posts = $this->model->read();
     if (empty($posts)) {
        http_response_code(404);
        echo json_encode(['message' => 'No posts found']);
        return;
    }
    \src\core\JsonResponse::success($posts);
    // http_response_code(200);
    // echo json_encode(['data' => $posts]);
  }
  public function getSinglePost(string $id){
    $post =$this ->model->getPostByIdTitleOrAuthor( $id);
    if (empty($post)) {
        http_response_code(404);
        echo json_encode(['message' => 'No posts found']);
        return;
    }
    \src\core\JsonResponse::success($post);
  }
  public function createPost(){
    
  }
}