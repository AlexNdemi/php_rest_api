<?php
namespace src\interfaces;

interface UsersModelContract{
    public function getAllUsers():array;

    public function insertUser(string $userId,string $name,string $mobile_no,string $email,string $password): void;

    public function checkUsername(string $username): bool;

    public function checkMobileNo(string $mobileNo): bool;

    public function checkAccount(string $emailOrMobileNo):?array;

    public function checkEmail(string $email): bool;






}