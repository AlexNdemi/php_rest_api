<?php
declare(strict_types=1);
namespace src\api;
use src\attributes;
use src\controllers\PostsController;
use src\core\JsonResponse;

#[attributes\RouteConstructor(classArgs: [
    [
        PostsController::class,[\src\interfaces\PostsModelContract::class]
    ]
  ]
   )
]
Class CreateRoute{
    public function __construct(private PostsController $postsController){       
    }

    #[attributes\Post(route:"/create")]
    public function create(){
      JsonResponse::sendCorsHeaders(['POST', 'OPTIONS']);
      header("Content-Type: application/json; charset=UTF-8");

    // ✅ Handle preflight request (OPTIONS)
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204); // No Content
        exit;
    }

    // Read JSON input
      $data = json_decode(file_get_contents("php://input"), true);

      if (!is_array($data)) {
        JsonResponse::error("Invalid JSON input.");
        return;
     }

      if (
      !$this->noEmptyInputs($data)
      ) {
        return;
      }
        $this->postsController->createPost(
        category_id: $data['category_id'],
        title: $data['title'],
        body: $data['body'],
        author: $data['author']
      );

        JsonResponse::success([
          'success'=>true,
          'message'=>'Post created successfully'
        ]);
      }

       private function noEmptyInputs(array $data): bool
{
    $errors = [];
    $normalized = [
        'category_id' => $data['category_id'] ?? '',
        'title' => $data['title'] ?? '',
        'body' => $data['body'] ?? '',
        'author' => $data['author'] ?? ''
    ];
    if (trim($normalized['title']) === '') {
        $errors['title'] = '';
    }
    if (trim($normalized['body']) === '') {
        $errors['body'] = '';
    }
    if (trim($normalized['author']) === '') {
        $errors['author'] = '';
    }

    if (!empty($errors)) {
        // Only exclude the keys that had errors from the valid values
        $valid = array_diff_key($normalized, $errors);

        JsonResponse::error(
            message: "Missing required fields",
            extra: array_merge($errors, $valid)
        );
        return false;
    }

    return true;
}


      
    }

   


    