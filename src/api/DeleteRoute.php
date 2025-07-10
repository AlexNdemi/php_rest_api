<?php
declare(strict_types=1);
namespace src\api;

use src\attributes;
use src\controllers\PostsController;
use src\core\JsonResponse;
use src\core\AuthMiddleware;

#[attributes\RouteConstructor(classArgs: [
    [
        PostsController::class,[\src\interfaces\PostsModelContract::class]
    ]
  ]
   )
]
class DeleteRoute{
  public function __construct(private PostsController $postsController){       
  }

   #[attributes\Delete(route:"/delete/{id}")]
    public function delete(string $id){
      JsonResponse::sendCorsHeaders(allowedMethods: ['DELETE']);

      $user = AuthMiddleware::auth();

      if(!is_numeric(value: $id)){
        JsonResponse::error(message: "Invalid input.");
        return;
      }

      $this->postsController->delete($id);

      JsonResponse::success([
          'success'=>true,
          'message'=>'Post deleted successfully'
        ]);
      }



  }