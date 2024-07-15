<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminHomeController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/home', name: 'app_admin_home')]
    public function index(Security $security): Response
    {
        return $this->render('admin/index.html.twig', [
            'admin'=>$this->getUser()->getUserIdentifier()
        ]);
    }
}
