<?php

namespace App\Tests\Integration;

use App\Entity\Event;
use App\Entity\User;
use App\Factory\EventFactory;
use App\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class HomeControllerIntegrationTest extends WebTestCase
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


    public function testToggleInterested()
    {
        // Create a user and an event
        $user = $this->userFactory->createUser('test-user@test.com', 'password');
        $event = $this->eventFactory->createEvent();

        // Persist entities
        $entityManager = $this->em;
        $entityManager->persist($user);
        $entityManager->persist($event);
        $entityManager->flush();
        // Log in user
        $this->client->loginUser($user);

        // Initial state check
        $this->assertFalse($user->getInterestedIn()->contains($event),'user shouldnt be interested initially');
        $this->assertSame(0, $event->getInterested(),'event shouldnt have interested people initially');

        // Add interest
        $crawler = $this->client->request('GET', '/event/'.$event->getId().'/interested');
        // Refresh entities to get updated state from the database
        $entityManager->refresh($entityManager->getRepository(User::class)->findOneBy(['email'=>'test-user@test.com']));
        $entityManager->refresh($entityManager->getRepository(Event::class)->findOneBy(['name'=>'test-event']));
        $entityManager->flush();

        // Verify user is interested and event interest count has increased
        $this->assertTrue($user->getInterestedIn()->contains($event),'user should be interested');
        $this->assertGreaterThan(0, $event->getInterested(),'event should now have an interested person');


        // Remove interest
        $crawler = $this->client->request('GET', '/event/'.$event->getId().'/interested');
        $this->assertResponseRedirects('/event/'.$event->getId());
        $crawler = $this->client->followRedirect();
        $entityManager->refresh($entityManager->getRepository(User::class)->findOneBy(['email'=>'test-user@test.com']));
        $entityManager->refresh($entityManager->getRepository(Event::class)->findOneBy(['name'=>'test-event']));
        $entityManager->flush();
        // Verify user is no longer interested and event interest count is 0
        $this->assertFalse($user->getInterestedIn()->contains($event),"user shouldnt have events in intersted anymore");
        $this->assertEquals(0, $event->getInterested(),'event shouldnt have any more intersted people ');
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