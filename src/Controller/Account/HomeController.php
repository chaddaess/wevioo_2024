<?php

namespace App\Controller\Account;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Security("is_granted('ROLE_MEMBER')")]
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('account/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
