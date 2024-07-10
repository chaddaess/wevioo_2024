<?php

namespace App\Factory;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class UserFactory
{

    public function __construct(private UserPasswordHasherInterface $passwordHasher){

    }
    public function createUser(string $email,string $password):User{
        $user=new User();
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user,$password));
        $user->setRoles(['ROLE_USER']);
        return $user;
    }
}