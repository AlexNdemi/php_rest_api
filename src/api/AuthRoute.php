<?php
namespace src\api;
use src\attributes;
use src\controllers\UsersController;
use src\core\JsonResponse;
use src\services\TokenService;

#[attributes\RouteConstructor(classArgs:[
  [
    UsersController::class,[\src\interfaces\UsersModelContract::class]
  ]
])]
class AuthRoute{
 

  public function __construct(private UsersController $usersController){       
    }
  
   #[attributes\Post(route:"/signup")]
  public function signUp():void
  {
     JsonResponse::sendCorsHeaders(['POST']);
    $data = json_decode(file_get_contents("php://input"), true);
    
     if (!is_array($data)) {
        JsonResponse::error("Invalid JSON input.");
        return;
     }

     if(
      !$this->emptySignUpInputs($data)&& !$this->invalidName($data)&& !$this->invalidEmail($data) &&
      $this->isPasswordLongEnough($data) &&
      $this->passwordsDontMatch($data) &&
      !$this->userAlreadyExists($data)
      ){

      $data = array_map('trim',$data);
      $this->usersController->addUser($data["userId"],$data["username"],$data["mobileNo"],$data["email"],$data["password"]);
      
      JsonResponse::success([
        'success'=>true,
        'message'=>"Your account has been successfully created"
      ]);
      }
  }
  #[attributes\Post(route:"/login")]
  public function logIn(): void{
    JsonResponse::sendCorsHeaders(['POST']);
    $data = json_decode(file_get_contents("php://input"), true);
    
     if (!is_array($data)) {
        JsonResponse::error("Invalid JSON input.");
        return;
     }

     if(!$this->emptyLoginInputs($data) && !$this->invalidPhoneNumberOrEmail($data)){
      $user=$this->usersController->getUserDetails($data["emailOrMobileNo"]);

      if(!$user){

        $errors = ['emailOrPassword' => 'account does not exist'];
        $valid = $data;

        JsonResponse::error(
          message:'account does not exist',
          extra: $errors + $valid,
        );
        exit();

      }
      $pwdHashed =$user["password"];

      $checkPwd = password_verify($data["password"],$pwdHashed);

      if(!$checkPwd){
        $errors = ['password' => 'incorrect passwords'];
        $valid = $data;

        JsonResponse::error(
          message:'incorrect passwords',
          extra: $errors + $valid,
        );
        exit();
      }
      $accessToken = TokenService::generateAccessToken($user);
      $refreshToken = TokenService::generateRefreshToken($user);

      setcookie('refresh_token', $refreshToken, [
            'httponly' => true,
            'secure' => false, // change to true in production
            'path' => '/',
            'expires' => time() + 1209600
        ]);

        JsonResponse::success(['accessToken' => $accessToken]);


     }
  }


    #[attributes\Post(route:"/refresh")]
  public function refresh()
  {
    $refreshToken = $_COOKIE['refresh_token'] ?? '';

    if (!$refreshToken) {
         JsonResponse::error("No refresh token", 403);
         exit();
    }

    try {
        $decoded = TokenService::verify($refreshToken);

        if ($decoded->type !== 'refresh') {
            throw new \Exception("Invalid token type");
        }

        $user = ['id' => $decoded->sub, 'email' => $decoded->email]; 
        
        $accessToken = TokenService::generateAccessToken($user);
        $refreshToken = TokenService::generateRefreshToken($user);

        setcookie("refresh_token", $refreshToken, [
            'httponly' => true,
            'secure' => true,
            'path' => '/',
            'expires' => time() + 1209600
        ]);

        return JsonResponse::success(['accessToken' => $accessToken]);
    } catch (\Exception $e) {
        return JsonResponse::error("Invalid refresh token", 403);
    }
}

#[attributes\Post(route:"/logout")]
public function logout(): void
{
    $refreshToken = $_COOKIE['refresh_token'] ?? '';
    if ($refreshToken) {
        file_put_contents(__DIR__ . '/../../storage/blacklist.txt', $refreshToken . "\n", FILE_APPEND);
    }

    setcookie('refresh_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'httponly' => true,
        'secure' => false
    ]);

    JsonResponse::success(['message' => 'Logged out']);
}


  private function emptySignUpInputs(array $data): bool{
      
      $normalized = [
        'username'=> $data['username'] ?? '',
        'mobileNo'=> $data['mobileNo'] ?? '',
        'email'=> $data['email'],
        'password'=> $data['password'] ?? '',
        'passwordRepeat'=> $data['passwordRepeat'] ?? ''

      ];
      $errors = array_filter($normalized,fn(string $str)=> trim($str)==='');

      if($errors){
        $errors = array_fill_keys($errors,'');
        $valid = array_diff_key($normalized,$errors);

        JsonResponse::error(
          message:'Missing required fields',
          extra: $errors + $valid,
        );
        exit();
      }
    return  false;
  }
  private function invalidName($data): bool{

    if(!preg_match("/^[A-Z][a-zA-Z]{3,}(?: [A-Z][a-zA-Z]*){0,2}$/",$data["username"])){
      $errors = [];
      $errors["username"] = "invalid username"; 
      $valid = $data;
      JsonResponse::error(
        message:'please enter a valid name',
        extra: $errors + $valid,
      );
      exit();
    }
    return false;
  }

  private function isPasswordLongEnough(array $data): bool
{
    
    $normalized = ['password' => $data['password'] ?? ''];

    $errors = array_filter(
        $normalized,
        fn($p) => mb_strlen($p) < 8
    );

    if ($errors) {
        $errors = ['password' => 'must be ≥ 8 chars'];
        JsonResponse::error(
            message: 'Password must have at least 8 characters',
            extra:   $errors + $data
        );
        exit();
    }
    return true;
}


  private function invalidPhoneNumberOrEmail(array $data):bool{

    $normalized = ['emailOrMobileNo' => $data['emailOrMobileNo'] ?? ''];

     $errors = array_filter(
        $normalized,
        fn($p) => (bool) filter_var($p,FILTER_VALIDATE_EMAIL) === false && (bool) preg_match("/^\d+$/",$p) === false
      );

      if($errors){
        $errors = ['emailOrMobileNo' => 'Invalid email or phone number'];
        JsonResponse::error(
            message: 'Invalid email or phone number',
            extra:   $errors + $data
        );
        exit();
        
      }
    return false;
  }

  private function invalidEmail(array $data): bool{
    $normalized = ['email'=>$data["email"] ?? ''];

    $errors = array_filter($normalized,fn(string $e)=>(bool) filter_var($e,FILTER_VALIDATE_EMAIL) === false);

    if($errors){
      $errors = ['email' => 'Invalid email'];
        JsonResponse::error(
            message: 'Invalid email',
            extra:   $errors + $data
        );
      exit();
    }
    return false;
  }

  private function passwordsDontMatch($data):bool{
    $normalized = [
      'password'=>$data["password"] ?? '',
      'passwordRepeat'=>$data["passwordRepeat"] ?? ''
    ];
    $error = $normalized["password"] !== $normalized["passwordRepeat"];
    
    if($error){
      $errors = ['passwordRepeat'=>"passwords don't match"];
      JsonResponse::error(
        message:"passwords don't match",
        extra:$errors + $data
      );
      exit();
    }

    return false;
  }

  private function userAlreadyExists($data):bool{
    $normalized = [
      'email'=>$data["email"] ?? '',
      'mobileNo'=>$data["mobileNo"] ?? ''
    ];

    
    if($this->usersController->isEmailAlreadyInUse($normalized["email"]) ){
      $errors =['email'=>"email already in use"];
      JsonResponse::error(
        message:"email already in use",
        extra:$errors + $data
      );
      exit();
    };

    if($this->usersController->isMobileNumberAlreadyInUse($normalized["mobileNo"])){
      $errors =['mobileNo'=>"mobileNo already in use"];
      JsonResponse::error(
        message:"mobileNo  already in use",
        extra:$errors + $data
      );
      exit();
    }
    return false;
    
  }

  public function emptyLoginInputs(array $data): bool{
    $normalized = [
        'emailOrMobileNo'=> $data['emailOrMobileNo'] ?? '',
        'password'=> $data['password'] ?? '',
      ];

      $errors = array_filter($normalized,fn(string $str)=> trim($str)==='');

      if($errors){
        $errors = array_fill_keys($errors,'');
        $valid = array_diff_key($normalized,$errors);

        JsonResponse::error(
          message:'Missing required fields',
          extra: $errors + $valid
        );
         exit();
      }

      return false;
  }


}
