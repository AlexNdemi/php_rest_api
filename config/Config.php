<?php
namespace config;
Class Config{
  protected array $config=[];
 public function __construct(public array $env){
    $this->config=[
       "db"=>[
        "DATABASE_HOSTNAME" => $env["DATABASE_HOSTNAME"],
        "DATABASE_USERNAME" => $env["DATABASE_USERNAME"],
        "DATABASE_PASSWORD" => $env["DATABASE_PASSWORD"],
        "DATABASE_NAME" => $env["DATABASE_NAME"]
       ],
       "JWT_SECRET" =>$env["JWT_SECRET"]
    ];

 }
 public function __get($name){
  return $this->config[$name] ?? null;
 }
}