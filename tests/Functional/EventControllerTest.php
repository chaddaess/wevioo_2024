<?php

use App\Entity\AdminUser;
use App\Entity\User;
use App\Factory\AdminFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Factory\UserFactory;
use Symfony\Component\HttpFoundation\Response;

class EventControllerTest extends WebTestCase
{
    private ?UserFactory $userFactory;
    private ?EntityManagerInterface $entityManager;
    private ?AdminFactory $adminFactory;
    private $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->userFactory = $this->client->getContainer()->get(UserFactory::class);
        $this->adminFactory=$this->client->getContainer()->get(AdminFactory::class);
        $this->entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);
    }

    public function test_access_to_event_index_for_non_admins_redirects_to_login()
    {
        $user = $this->userFactory->createUser('nonadmin@example.com', 'password');
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->client->loginUser($user);
        $this->client->request('GET', '/admin/event');
        if ($this->client->getResponse()->isRedirect()) {
            $this->client->followRedirect();
        }
        //in case the server didn't redirect directly
        $this->client->followRedirect();
        $this->assertRouteSame('app_admin_login');
    }

    public function test_access_to_event_index_for_admins_successful(){
        $admin=$this->adminFactory->createAdmin('test-admin','password');
        $this->entityManager->persist($admin);
        $this->entityManager->flush();
        $this->client->loginUser($admin);
        $this->client->request('GET','/admin/event');
        if ($this->client->getResponse()->isRedirect()) {
            $this->client->followRedirect();
        }
        //in case the server didn't redirect directly
        $this->client->followRedirect();
        $this->assertRouteSame('app_event_index');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'nonadmin@example.com']);
        $admin=$this->entityManager->getRepository(AdminUser::class)->findOneBy(['username' => 'test-admin']);
        if ($user) {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        }
        if($admin){
            $this->entityManager->remove($admin);
            $this->entityManager->flush();
        }
        $this->entityManager->clear();
    }
}
