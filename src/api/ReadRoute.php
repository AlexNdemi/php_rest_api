<?php
declare(strict_types=1);
namespace src\api;

use src\views\PostsView;
use src\attributes;
use src\core;

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
         core\JsonResponse::sendCorsHeaders(allowedMethods:['GET']);

        $name = $queryParams['name'] ?? null;
        $author = $queryParams['author'] ?? null;

        if ($name) {
            return $this->postsView->getSinglePost($name);
        }

        if ($author) {
            return $this->postsView->getSinglePost($author);
        } 

        return $this->postsView->getAllPosts();
    }

    
    #[attributes\Route(route:"/read/{id}")]
    public function readSinglePost (string $id,array $queryParams = []) {
    core\JsonResponse::sendCorsHeaders(allowedMethods:['GET']);
    return $this->postsView->getSinglePost($id);
    }
    
}






