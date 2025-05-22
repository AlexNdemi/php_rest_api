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
    public function read(){
         header('Access-Control-Allow-Origin: *');
         header('Content-Type: application/json');
        return $this->postsView->getAllPosts();
    }
}






