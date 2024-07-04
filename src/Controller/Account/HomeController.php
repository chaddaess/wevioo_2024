<?php

namespace App\Controller\Account;

use App\Service\TypeSenseService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{

    public function __construct(private TypeSenseService $typeSenseService){

    }
    #[IsGranted('ROLE_USER')]
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
//        $response=$this->typeSenseService->search('events','',['location'=>'(40.750502, -73.993439,5km)']);
//        dd($response);


        return $this->render('account/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
