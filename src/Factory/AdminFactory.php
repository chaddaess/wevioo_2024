<?php

namespace App\Factory;

use App\Entity\AdminUser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminFactory
{
    public function __construct(private readonly  UserPasswordHasherInterface $passwordHasher){

    }

    public function createAdmin(string $username,string $password):AdminUser{
        $admin=new AdminUser();
        $hashedPassword=$this->passwordHasher->hashPassword($admin,$password);
        $admin->setUsername($username);
        $admin->setPassword($hashedPassword);
        $admin->setRoles(['ROLE_ADMIN','ROLE_USER']);
        return $admin;
    }


}