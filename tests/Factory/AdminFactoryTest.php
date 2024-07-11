<?php

namespace App\Tests\Factory;

use App\Entity\AdminUser;
use App\Factory\AdminFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminFactoryTest extends TestCase
{
    public function test_creates_an_admin(){
        $username='test-user';
        $password='password';
        $passwordHasher=$this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher
            ->expects($this->any())
            ->method('hashPassword')
            ->willReturn('brika');
        $factory=new AdminFactory($passwordHasher);
        $admin=$factory->createAdmin($username,$password);
        $this->assertInstanceOf(AdminUser::class,$admin);
        $this->assertSame($username,$admin->getUsername());
        $this->assertSame('brika',$admin->getPassword());
        $this->assertSame(['ROLE_USER','ROLE_ADMIN'],$admin->getRoles());
    }
}