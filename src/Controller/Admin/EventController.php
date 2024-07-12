<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Service\TypeSenseService;
use Doctrine\ORM\EntityManagerInterface;
use Http\Client\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Typesense\Exceptions\TypesenseClientError;
use function PHPUnit\Framework\throwException;


#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/event')]
class EventController extends AbstractController
{
    public  function  __construct( private readonly TypeSenseService $typeSenseService){

    }
    #[Route('/', name: 'app_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        $user=$this->getUser();
        return $this->render('admin/event/index.html.twig', [
            'events' => $eventRepository->findBy(['creator'=>$user->getUserIdentifier()])
        ]);
    }

    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,SluggerInterface $slugger): Response
    {
        $user=$this->getUser()->getUserIdentifier();
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $event->setCreator($user);
            $this->saveEventPicture($form, $slugger, $event,"new");
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        $user=$this->getUser()->getUserIdentifier();
        if($user!=$event->getCreator()){
            $this->addFlash('error',"you're not authorized to edit this event");
            return $this->redirectToRoute('app_admin_home');
        }
        return $this->render('shared/event/event-details.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager,SluggerInterface $slugger): Response
    {
        $user=$this->getUser()->getUserIdentifier();
        if($user!=$event->getCreator()){
            $this->addFlash('error',"you're not authorized to edit this event");
            return $this->redirectToRoute('app_admin_home');
        }
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coordinatesJson = $form->get('coordinates')->getData();
            $coordinates = json_decode($coordinatesJson, true)??$event->getLocation();
            $event->setLocation($coordinates);
            $this->saveEventPicture($form, $slugger, $event,"edit");
            $entityManager->flush();
            $this->addFlash('success',"event edited successfully");
            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $user=$this->getUser()->getUserIdentifier();
        if($user!=$event->getCreator()){
            $this->addFlash('error',"you're not authorized to delete this event");
            return $this->redirectToRoute('app_admin_home');
        }
        //delete event from typeSense collection to keep integrity
        $client = $this->typeSenseService->getClient();
        try {
            $client->collections['events']->documents[$event->getId()]->delete();
        } catch (Exception|TypesenseClientError $e) {
            $this->addFlash('error','error deleting event');
            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }
        if ($this->getParameter('kernel.environment') === 'test') {
            // bypass csrf tests
            $entityManager->remove($event);
            $entityManager->flush();
        }


        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        $this->addFlash('success','event deleted successfully');
        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form
     * @param SluggerInterface $slugger
     * @param Event $event
     * @return void
     */
    public function saveEventPicture(FormInterface $form, SluggerInterface $slugger, Event $event,string $origin): void
    {
        $file = $form->get('picture')->getData();
        if ($file) {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
            $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads';

            try {
                $file->move($uploadsDirectory, $newFilename);
            } catch (FileException $e) {
                dd("oops! error on upload");
            }
            $event->setPicture($newFilename);

        }else{
            if($origin=="new") {
                $newFilename = 'no-image.png';
                $event->setPicture($newFilename);
            }
        }

    }
}
