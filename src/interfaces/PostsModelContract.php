<?php
namespace src\interfaces;
interface PostsModelContract{
  public function read():array;
  public function getPostByIdOrAuthor(string $id):array;

  public function create(string $category_id,string $title,string $body,string $author): void;

  public function update(int $id,string $category_id,string $title,string $body,string $author):void;

   public function Delete (int $id):void;

}