<?php

namespace App\Controller\Account;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('account/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
