<?php

namespace App\Controller\Account;

use App\Entity\Event;
use App\Entity\User;
use App\Service\EventService;
use App\Service\TypeSenseService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;

#[IsGranted('ROLE_USER')]
class HomeController extends AbstractController
{

    public function __construct(private readonly TypeSenseService $typeSenseService,private  readonly EventService $eventService,private EntityManagerInterface $entityManager){

    }

    #[Route('/explore', name: 'app_home')]
    public function index(Request $request): Response
    {
        $hits=[];
        $filters=[];
        if ($request->isMethod('POST')) {
//            dd($request->request->get('coordinates'));
            $keywords=$request->request->get('keywords')?$request->request->get('keywords'):"*";
            $category=$request->request->get('category')?$request->request->get('category'):null;
            $location=$request->request->get('coordinates')?json_decode($request->request->get('coordinates')):null;
            $date=$request->request->get('dates')?$this->calculateDate($request->request->get('dates')):null;

            if($category){
                $filters['category']=$category;
            }
            if($date){
                $filters['date']='<='.$date.'&& date >='.(new \DateTime())->getTimestamp();
            }
            if($location){
                $filters['location']='('.$location[0].','.$location[1].',120km)';
            }
            $response=$this->typeSenseService->search('events',$keywords,$filters);
            $hits=$response;
//            dd($response);

        }

        return $this->render('account/index.html.twig', [
            'controller_name' => 'HomeController',
            'eventCategories'=>$this->eventService->getEventCategories(),
            'events'=>$hits
        ]);
    }

    #[Route('/event/{id}', name: 'app_user_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        $email=$this->getUser()->getUserIdentifier();
        $user=$this->entityManager->getRepository(User::class)->findOneBy(['email'=>$email]);
        $isInterested = $user->getInterestedIn()->contains($event);
        $isAttending = $user->getGoingTo()->contains($event);
        return $this->render('shared/event/event-details.html.twig', [
            'event' => $event,
            'isInterested' => $isInterested,
            'isAttending' => $isAttending,
            'locationArray'=>$event->getLocation(),
        ]);
    }
    #[Route('/event/{id}/interested', name: 'app_user_event_toggle_interested', methods: ['GET'])]
    public function toggleInterested(Event $event): Response
    {
        $email=$this->getUser()->getUserIdentifier();
        $user=$this->entityManager->getRepository(User::class)->findOneBy(['email'=>$email]);
        if(!in_array($event,$user->getInterestedIn()->toArray())){
            $user->addInterestedIn($event);
            $event->setInterested($event->getInterested()+1);
        }else{
            $user->removeInterestedIn($event);
            $event->setInterested($event->getInterested()-1);

        }
        $this->entityManager->flush();
        return $this->redirectToRoute('app_user_event_show',[
            'id'=>$event->getId()
        ]);
    }

    #[Route('/event/{id}/going', name: 'app_user_event_toggle_going', methods: ['GET'])]
    public function toggleGoing(Event $event): Response
    {
        $email=$this->getUser()->getUserIdentifier();
        $user=$this->entityManager->getRepository(User::class)->findOneBy(['email'=>$email]);
        if(!in_array($event,$user->getGoingTo()->toArray())){
            $user->addGoingTo($event);
            $event->setAttending($event->getAttending()+1);
        }else{
            $user->removeGoingTo($event);
            $event->setAttending($event->getAttending()-1);

        }
        $this->entityManager->flush();
        return $this->redirectToRoute('app_user_event_show',[
            'id'=>$event->getId()
        ]);
    }
    #[Route('/myevents',name:'app_user_my_events')]
    public function myEvents():Response
    {
        $email=$this->getUser()->getUserIdentifier();
        $user=$this->entityManager->getRepository(User::class)->findOneBy(['email'=>$email]);
        $interested=$user->getInterestedIn();
        $going=$user->getGoingTo();
        return $this->render('account/event/my-events.html.twig',[
            'going'=>$going,
            'interested'=>$interested,
        ]);
    }

    private function calculateDate(string $dateOption){
        $now =new \DateTime();
        switch ($dateOption){
            case 'today':
                $endDate=(clone  $now)->setTime(23,59,59);
                break;
            case 'this week':
                $endDate=(clone $now)->modify('sunday this week')->setTime(23,59,59);
                break;
            case 'this month':
                $endDate=(clone $now)->modify('last day of this month')->setTime(23,59,59);
                break;
            case 'this year':
                $endDate = (clone $now)->setDate($now->format('Y'), 12, 31)->setTime(23, 59, 59);
                break;
        }
        return($endDate->getTimestamp());
    }
}
