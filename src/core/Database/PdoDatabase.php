<?php
namespace src\core\Database;

use config;
use Dotenv;
Class PdoDatabase {
  private config\Config $config;
  private  \PDO $pdo;
  public function __construct() {
    $envPath = __DIR__ ."/../../../";
    $dotenv = Dotenv\Dotenv::createImmutable($envPath);
    $dotenv->load();
    $this->config = new config\Config($_ENV);
    $db = $this->config->db;
    try{
      $dsn = 'mysql:host='.
      $db["DATABASE_HOSTNAME"].';dbname='.
      $db["DATABASE_NAME"];
      $this->pdo = new \PDO($dsn,
      $db["DATABASE_USERNAME"],
      $db["DATABASE_PASSWORD"]);
                $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE,\PDO::FETCH_ASSOC);
    }catch(\PDOException $e){
      throw new \PDOException($e->getMessage(),$e->getCode());
  }
  }
  public function db(): \PDO{
    return $this->pdo;
  }
  public function __call($name, $args){
    return call_user_func_array([$this->pdo,$name],$args);
  }

}