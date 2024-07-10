<?php

use App\Entity\AdminUser;
use App\Entity\Event;
use App\Entity\User;
use App\Factory\AdminFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Factory\UserFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
        $this->adminFactory = $this->client->getContainer()->get(AdminFactory::class);
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

    public function test_access_to_event_index_for_admins_successful()
    {
        $admin = $this->adminFactory->createAdmin('test-admin', 'password');
        $this->entityManager->persist($admin);
        $this->entityManager->flush();
        $this->client->loginUser($admin);
        $this->client->request('GET', '/admin/event');
        if ($this->client->getResponse()->isRedirect()) {
            $this->client->followRedirect();
        }
        $this->assertRouteSame('app_event_index');
    }

    public function test_show_event_for_admin_successful(): void
    {
        $admin = $this->adminFactory->createAdmin('test-admin', 'password');
        $this->entityManager->persist($admin);
        $this->entityManager->flush();
        $this->client->loginUser($admin);
        $event = new Event();
        $event->setName('test-event');
        $this->entityManager->persist($event);
        $this->entityManager->flush();
        $crawler = $this->client->request('GET', '/admin/event/' . $event->getId());

        if ($this->client->getResponse()->isRedirect()) {
            $this->client->followRedirect();
        }
        $this->assertResponseIsSuccessful();
    }

    public function test_show_event_for_admin_unsuccessful(): void
    {
        $admin = $this->adminFactory->createAdmin('test-admin', 'password');
        $this->entityManager->persist($admin);
        $this->entityManager->flush();
        $this->client->loginUser($admin);
        $event = new Event();
        $event->setName('test-event');
        $this->entityManager->persist($event);
        $this->entityManager->flush();
        $fakeId = "110";
        $crawler = $this->client->request('GET', '/admin/event/' . $fakeId);
        if ($this->client->getResponse()->isRedirect()) {
            $this->client->followRedirect();
        }
        $this->assertResponseStatusCodeSame(404);
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
