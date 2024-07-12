<?php

namespace App\Tests\Functional;
use App\Entity\Event;
use App\Factory\AdminFactory;
use App\Factory\EventFactory;
use App\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use function Symfony\Component\Translation\t;

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
        $admin=$this->adminFactory->createAdmin('test-admin','password');
        $event=$this->eventFactory->createEvent();
        $this->entityManager->persist($event);
        $this->entityManager->persist($admin);
        $this->entityManager->flush();
        $this->client->loginUser($admin,'admin');
        $this->client->followRedirects();
        $crawler=$this->client->request('GET','/admin/event/'.$event->getId());
        $this->assertTrue($crawler->filter('html:contains("<div class="flash error">\nyou&#039;re not authorized to edit this event\n</div>\n")')->count() > 0);
    }

    public function test_admin_can_create_a_valid_event(){
        $admin=$this->adminFactory->createAdmin('test-admin','password');
        $this->entityManager->persist($admin);
        $this->entityManager->flush();
        $this->client->loginUser($admin,'admin');
        $this->client->followRedirects();
        $crawler=$this->client->request('GET','/admin/event/new');
        $crawler = new Crawler(html_entity_decode($crawler->html()),'http://127.0.0.1:8000/admin/event/new');
        $form = $crawler->selectButton('Save')->form();
        $form['event[name]'] = 'test-event';
        $form['event[category]'] = 'Education';
        $form['event[date]'] = '2024-07-12T12:00';
        $form['event[comments]'] = 'This is a test event.';
        $form['event[picture]'] = new UploadedFile('public\images\test-picture.png', 'test-picture');;
        $form['event[coordinates]'] = json_encode([12, 34]);
        $form['event[address]'] = 'test-address';
        $crawler = $this->client->submit($form);
        $this->assertResponseIsSuccessful();
        // test if the event was saved to database
        $event=$this->entityManager->getRepository(Event::class)->findOneBy(['name'=>'test-event']);
        $this->assertNotNull($event,'event should be saved in the database');
        // test if  picture was added to the upload folder
        $this->assertFileExists('public/uploads/'.$event->getPicture());
        //delete the picture
        unlink('public/uploads/'.$event->getPicture());
    }

    public function test_admin_cant_submit_an_invalid_file(){
        $admin=$this->adminFactory->createAdmin('test-admin','password');
        $this->entityManager->persist($admin);
        $this->entityManager->flush();
        $this->client->loginUser($admin,'admin');
        $this->client->followRedirects();
        $crawler=$this->client->request('GET','/admin/event/new');
        $crawler = new Crawler(html_entity_decode($crawler->html()),'http://127.0.0.1:8000/admin/event/new');
        $form = $crawler->selectButton('Save')->form();
        $form['event[name]'] = 'test-event';
        $form['event[category]'] = 'Education';
        $form['event[date]'] = '2024-07-12T12:00';
        $form['event[comments]'] = 'This is a test event.';
        $form['event[picture]'] = new UploadedFile('public\data\events.jsonl', 'events.jsonl'); // Simulating invalid file
        $form['event[coordinates]'] = json_encode([12, 34]);
        $form['event[address]'] = 'test-address';
        $crawler = $this->client->submit($form);
        $this->assertTrue($crawler->filter('html:contains("<li>Please upload a valid image</li>")')->count() > 0);
        $event=$this->entityManager->getRepository(Event::class)->findOneBy(['name'=>'test-event']);
        $this->assertTrue(!$event);
    }

    public function test_cant_delete_another_admins_event(){
        $admin=$this->adminFactory->createAdmin('test-admin','password');
        $this->entityManager->persist($admin);
        $event=$this->eventFactory->createEvent();// event.creator!=test-admin
        $this->entityManager->persist($event);
        $this->entityManager->flush();
        $this->client->loginUser($admin,'admin');
        $this->client->followRedirects();
        $crawler=$this->client->request('GET','admin/event/'.$event->getId().'/delete');
        $this->assertTrue($crawler->filter('html:contains("<div class="flash error">\nyou&#039;re not authorized to delete this event\n</div>")')->count() > 0);
        $this->assertNotNull($this->entityManager->getRepository(Event::class)->findOneBy(['name'=>'test-event']));
    }






    protected function tearDown(): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->executeQuery('DELETE FROM event WHERE name = "test-event"');
        $connection->executeQuery('DELETE FROM admin_user WHERE username = "test-admin"');
        $connection->executeQuery('DELETE FROM user WHERE email = "nonadmin@example.com"');
        $this->entityManager->close();
        $this->entityManager = null;

        parent::tearDown();
    }
}
