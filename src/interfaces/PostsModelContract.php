<?php
namespace src\interfaces;
interface PostsModelContract{
  public function read():array;
  public function getPostByIdTitleOrAuthor(string $id):array;

}