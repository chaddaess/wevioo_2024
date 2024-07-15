<?php

namespace App\Command;

use App\Entity\Event;
use App\Service\TypeSenseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'typesense:load-event-data')]

class LoadTypeSenseEventDataCommand extends Command
{
    public function __construct(private TypeSenseService $typeSenseService,private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure():void
    {
        $this
            ->setDescription('Loads data to the events collection');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int{
        $events=$this->entityManager->getRepository(Event::class)->findAll();
        $documents=[];
        foreach ($events as $event){
            $documents[]=[
                'id'=>(string)$event->getId(),
                'name'=>$event->getName(),
                'date'=>$event->getDate()->getTimeStamp(),
                'category'=>$event->getCategory(),
                'picture'=>$event->getPicture(),
                'creator'=>$event->getCreator(),
                'attending'=>(int)$event->getAttending(),
                'interested'=>(int)$event->getInterested(),
                'ticketLink'=>$event->getTicketLink(),
                'comments'=>$event->getComments(),
                'location'=>$event->getLocation(),
                'address'=>$event->getAddress(),



            ];
        }
        $client=$this->typeSenseService->getClient();
        try{
            $client->collections['events']->documents->import($documents,['action' => 'upsert']);
            $output->writeln(count($documents)." events loaded successfully ✔️");
        }catch (\Exception $e){
            $output->writeln("error loading data ❌: ");
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }
        return Command::SUCCESS;


    }

}