<?php

namespace App\Tests\Functional;
use App\Entity\AdminUser;
use App\Entity\Event;
use App\Factory\AdminFactory;
use App\Factory\EventFactory;
use App\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FaultyEventControllerTest extends WebTestCase
{
    private ?UserFactory $userFactory;
    private ?EntityManagerInterface $entityManager;
    private ?AdminFactory $adminFactory;
    private ?EventFactory $eventFactory;

    private $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->userFactory = $this->client->getContainer()->get(UserFactory::class);
        $this->adminFactory = $this->client->getContainer()->get(AdminFactory::class);
        $this->eventFactory=$this->client->getContainer()->get(EventFactory::class);
        $this->entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);
    }

    public function test_authorized_admin_can_access_index(){
        $admin=$this->adminFactory->createAdmin('test-admin','password');
        $this->entityManager->persist($admin);
        $this->entityManager->flush();
        $this->client->loginUser($admin);
        $crawler=$this->client->request('GET','/admin/event');
        $this->assertResponseRedirects('http://localhost/admin/event/');
        $this->client->followRedirect();
    }
    public function test_unauthorized_user_cant_access_index(){
        $user=$this->userFactory->createUser('nonadmin@example.com','password');
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->client->loginUser($user);
        $this->client->followRedirects();
        $crawler=$this->client->request('GET','/admin/event');
        $this->assertResponseIsSuccessful();
        $this->assertTrue($crawler->filter('html:contains("<input type="submit" name="signin" id="signin" class="form-submit" value="Log in"/>\n")')->count() > 0);

    }
    public function  test_admin_cant_show_another_admins_event(){
        $this->client->disableReboot();
        $admin=$this->adminFactory->createAdmin('test-admin','password');
        $event=$this->eventFactory->createEvent();
        $this->entityManager->persist($event);
        $this->entityManager->persist($admin);
        $this->entityManager->flush();
        $this->client->loginUser($admin,'admin');
        dump($this->client->getContainer()->get('security.token_storage')->getToken());
        $crawler=$this->client->request('GET','/admin/event/'.$event->getId());
        $crawler=$this->client->followRedirect();
        $this->assertTrue($crawler->filter('html:contains("<div class="alert alert-danger">\nyou&#039;re not authorized to edit this event\n</div>\n")')->count() > 0);
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
