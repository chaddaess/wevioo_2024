<?php

namespace App\Tests\Functional;

use App\Entity\Event;
use App\Entity\User;
use App\Factory\AdminFactory;
use App\Factory\EventFactory;
use App\Factory\UserFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use function Symfony\Component\Translation\t;


class HomeControllerTest extends WebTestCase
{
    private $client;
    private ?EntityManagerInterface $em;
    private ?UserFactory $userFactory;
    private ?EventFactory $eventFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->userFactory = $this->client->getContainer()->get(UserFactory::class);
        $this->eventFactory=$this->client->getContainer()->get(EventFactory::class);
        $this->em = $this->client->getContainer()->get(EntityManagerInterface::class);
    }

    public function test_show_event_authenticated_user_successful()
    {
        $user=$this->userFactory->createUser('test-user@test.com','password');
        $event=$this->eventFactory->createEvent();
        $this->em->persist($user);
        $this->em->persist($event);
        $this->em->flush();
        $this->client->loginUser($user);
        $crawler=$this->client->request('GET', '/event/'.$event->getId());
        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('app_user_event_show');
        $this->assertTrue($crawler->filter('html:contains("<p class="text name">test-event</p>")')->count() > 0);
    }


    protected function tearDown(): void
    {
        $connection = $this->em->getConnection();
        $connection->executeQuery('DELETE FROM event WHERE name = "test-event"');
        $connection->executeQuery('DELETE FROM user WHERE email = "test-user@test.com"');

        $this->em->close();
        $this->em = null; //

        parent::tearDown();
    }
}