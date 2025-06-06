<?php
namespace src\interfaces;
interface PostsModelContract{
  public function read():array;
  public function getPostByIdTitleOrAuthor(string $id):array;

  public function create(string $category_id,string $title,string $body,string $author): void;

}