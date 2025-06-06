<?php
declare(strict_types=1);
namespace src\api;

use src\views\PostsView;
use src\attributes;

#[attributes\RouteConstructor(classArgs: [
    [
        PostsView::class,[\src\interfaces\PostsModelContract::class]
    ]
  ]
   )
]
Class ReadRoute{
    public function __construct(private PostsView $postsView){
   
    }
    
    #[attributes\Route(route: "/read")]
    public function read(array $queryParams = []){
         header('Access-Control-Allow-Origin: *');
         header('Content-Type: application/json');

        $name = $queryParams['name'] ?? null;
        $title = $queryParams['title'] ?? null;
        $author = $queryParams['author'] ?? null;

        if ($name) {
            return $this->postsView->getSinglePost($name);
        }

        if ($title) {
            return $this->postsView->getSinglePost($title);
        }

        if ($author) {
            return $this->postsView->getSinglePost($author);
        } 

        return $this->postsView->getAllPosts();
    }

    
    #[attributes\Route(route:"/read/{id}")]
    public function readSinglePost (string $id,array $queryParams = []) {
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    return $this->postsView->getSinglePost($id);
    }
    
}






