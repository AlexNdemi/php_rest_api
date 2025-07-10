<?php
namespace src\models\Pdo;

use PDO;
use src\core\Database\PdoDatabase;
use src\interfaces\UsersModelContract;

Class PdoUsersModel implements UsersModelContract{
  private string $table = 'users';

  private $conn;

  private function __construct(PdoDatabase $PdoDatabase){
    $this->conn = $PdoDatabase;
  }
  public function getAllUsers():array{
   $sql = "SELECT * FROM {$this->table}";
   $stmt = $this->conn->prepare($sql);
   $stmt->execute();
   $results = $stmt->fetchAll();
   $stmt = null;
   return $results;
  }
  public function insertUser(string $userId,string $name,string $mobile_no,string $email,string $password): void{
    $sql = "INSERT INTO {$this->table} (id,name, mobile_no, email,password) VALUES(?,?,?,?,?)";
    $hashedPswd = password_hash($password,PASSWORD_DEFAULT);
    $stmt = $this->conn->prepare($sql);
    $stmt ->execute([$userId,$name,$mobile_no,$email,$hashedPswd]);
    $stmt = null;
  }
  public function checkUsername(string $username): bool{
    $sql = "SELECT * FROM {$this->table} WHERE name = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$username]);
    $exists = $stmt->rowCount() > 0;
    $stmt = null;
    return $exists;

  }
  public function checkMobileNo(string $mobileNo): bool{
    $mobileNo=$this->parseMobileNo($mobileNo);
    $sql = "SELECT * FROM {$this->table} WHERE mobile_no LIKE ?";
    $stmt = $this->conn->prepare($sql);
    $pattern = "%$mobileNo";
    
    $stmt->execute([$pattern]);   
    $exists = $stmt->rowCount() > 0;
    return $exists;
}

  public function checkAccount(?string $emailOrMobileNo):?array{
    $possiblyAMobileNo = $this->parseMobileNo($emailOrMobileNo);
    echo $possiblyAMobileNo;
    // Use a conditional query based on whether we have a valid mobile number pattern
    if ($possiblyAMobileNo !== null) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? OR mobile_no LIKE ?";
        $pattern = "%$possiblyAMobileNo";
        $params = [$emailOrMobileNo, $pattern];
    } else {
        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
        $params = [$emailOrMobileNo];
    }


    $stmt = $this->conn->prepare($sql);

    $stmt->execute($params);
    $result=$stmt->fetch();
    return $result;

  }
  private function parseMobileNo(string $mobileNo): string|null{
      if(!preg_match("/^\d+$/",$mobileNo)){
        return null;
      };
      return substr($mobileNo, 0, 1) === '0' ? substr($mobileNo, 1) : $mobileNo;
  }
  public function checkEmail(string $email): bool{
    $sql = "SELECT * FROM {$this->table} WHERE email = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$email]);
    $exists = $stmt->rowCount() > 0;
    return $exists;
  }
  public function getUser(string $email){
    $sql = "SELECT * FROM {$this->table} WHERE email = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$email]);
    $results = $stmt->fetchAll();
    return $results;
  }
}