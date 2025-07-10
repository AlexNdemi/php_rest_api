<?php
namespace src\controllers;
use src\interfaces;
class UsersController{

  private $model;
  public function __construct(interfaces\UsersModelContract $model ){
    $this->model = $model;
  }

  public function getUserDetails(string $emailOrMobileNo): array{
    $userExists=$this->model->checkAccount($emailOrMobileNo);
    return $userExists;
  }

  public function isEmailAlreadyInUse(string $email): bool{   
    return $this->model->checkEmail($email);

  }
  public function isMobileNumberAlreadyInUse(string $mobileNo): bool{
    return $this->model->checkMobileNo($mobileNo);
  }
  public function addUser(string $userId,string $username,string $mobileNo,string $email,string $password): void{
    $data = array_map([$this,'clean'],[
      'userId'=>$userId,'username'=>$username,'mobileNo'=>$mobileNo,'email'=>$email,'password'=>$password
    ]);
  
    $this->model->insertUser($data["userId"],$data["username"],$data["mobileNo"],$data["email"],$data["password"]);
    
    
  }  

  private function clean(string $value): string {
    return htmlspecialchars(strip_tags($value));
  }


}