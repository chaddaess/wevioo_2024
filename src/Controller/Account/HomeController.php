<?php

namespace App\Controller\Account;

use App\Entity\Event;
use App\Service\EventService;
use App\Service\TypeSenseService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{

    public function __construct(private readonly TypeSenseService $typeSenseService,private  readonly EventService $eventService){

    }


    public function getCoordinates(string $location):array{
        //TODO: implement this function after integrating google maps api
        //dummy data for testing
        $coordinates=[48.86093481609114, 2.33698396872901];
        return $coordinates;
    }
    #[IsGranted('ROLE_USER')]
    #[Route('/explore', name: 'app_home')]
    public function index(Request $request): Response
    {
        $hits=[];
        $filters=[];
        if ($request->isMethod('POST')) {
            $keywords=$request->request->get('keywords')?$request->request->get('keywords'):"*";
            $category=$request->request->get('category')?$request->request->get('category'):null;
            $location=$request->request->get('location')?$this->getCoordinates($request->request->get('location')):null;
            $date=$request->request->get('dates')?$this->calculateDate($request->request->get('dates')):null;

            if($category){
                $filters['category']=$category;
            }
            if($date){
                $filters['date']='<='.$date;
            }
            if($location){
                $filters['location']='('.$location[0].','.$location[1].',5km)';
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
        return $this->render('account/event/event-details.html.twig', [
            'event' => $event,
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
