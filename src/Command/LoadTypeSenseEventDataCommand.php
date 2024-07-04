<?php

namespace App\Command;

use App\Service\TypeSenseService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'typesense:load-event-data')]

class LoadTypeSenseEventDataCommand extends Command
{
    public function __construct(private TypeSenseService $typeSenseService)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Loads data to the events collection');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int{
        $client=$this->typeSenseService->getClient();
        $eventsData = file_get_contents('public/data/events.jsonl');
        try{
            $client->collections['events']->documents->import($eventsData);
            $output->writeln("data loaded successfully");
        }catch (\Exception $e){
            $output->writeln("error loading data");
            $output->writeln($e->getMessage());
            return Command::FAILURE;

        }
        return Command::SUCCESS;


    }

}