<?php

namespace App\Tests\Functional;
use App\Entity\AdminUser;
use App\Entity\Event;
use App\Factory\AdminFactory;
use App\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FaultyEventControllerTest extends WebTestCase
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
        $this->adminFactory = $this->client->getContainer()->get(AdminFactory::class);
        $this->entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);
    }

    public function test_authorized_admin_can_access_index(){
        $admin=$this->adminFactory->createAdmin('test-admin','password');
        $this->entityManager->persist($admin);
        $this->entityManager->flush();
        $this->client->loginUser($admin);
        $crawler=$this->client->request('GET','/admin/event');
        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('app_event_index');
        $this->assertTrue($crawler->filter('html:contains("<h1>My events</h1>")')->count() > 0);
    }

    public function test_unauthorized_user_cant_access_index(){
        $user=$this->userFactory->createUser('nonadmin@example.com','password');
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->client->loginUser($user);
        $crawler=$this->client->request('GET','/admin/event');
        $this->assertResponseRedirects('app_admin_login');
    }

    protected function tearDown(): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->executeQuery('DELETE FROM event WHERE name = "test-event"');
        $connection->executeQuery('DELETE FROM admin_user WHERE username = "test-admin"');
        $connection->executeQuery('DELETE FROM user WHERE email = "nonadmin@example.com"');

        $this->entityManager->close();
        $this->entityManager = null; //

        parent::tearDown();
    }
}
