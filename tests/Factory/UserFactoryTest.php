<?php

namespace App\Tests\Factory;

use App\Entity\User;
use App\Factory\UserFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFactoryTest extends TestCase
{
    public function testItCreatesAUser(){
        $passwordHasher=$this->createMock(UserPasswordHasherInterface::class);
        //define the behaviour of method "hashPassword" for the mock hasher
        $passwordHasher
            ->expects($this->any())
            ->method('hashPassword')
            ->willReturn('brika');

        $factory=new UserFactory($passwordHasher);
        $user=$factory->createUser("test-user@gmail.com","test-password");
        $this->assertInstanceOf(User::class,$user);
        $this->assertSame("test-user@gmail.com", $user->getEmail());
        $this->assertSame(['ROLE_USER'], $user->getRoles());
        $this->assertSame('brika', $user->getPassword());

    }

}