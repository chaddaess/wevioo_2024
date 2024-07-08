<?php

namespace App\Command;

use App\Service\TypeSenseService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'typesense:set-event-schema')]
class SetTypeSenseEventSchema extends Command
{

    public function __construct(private TypeSenseService $typeSenseService)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates the Typesense schema for the events collection');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $eventSchema=[
            'name'=>'events',
            'fields'=>[
                ['name'=>'id','type'=>'int32'],
                ['name'=>'name','type'=>'string'],
                ['name'=>'date','type'=>'int64'],
                ['name'=>'category','type'=>'string'],
                ['name'=>'picture','type'=>'string'],
                ['name'=>'creator','type'=>'string'],
                ['name'=>'interested','type'=>'int32'],
                ['name'=>'attending','type'=>'int32'],
                ['name'=>'ticketLink','type'=>'string'],
                ['name'=>'comments','type'=>'string'],
                [
                    'name'  => 'location',
                    'type'  => 'geopoint'
                ],
                ['name'=>'address','type'=>'string'],



                [
                    'name'=>'embedding',
                    'type'=>'float[]',
                    'embed'=>[
                        'from'=>['name','category','creator'],
                        'model_config'=>[
                            'model_name'=>'ts/all-MiniLM-L12-v2'
                        ]
                    ]
                ]

            ],
            'default_sorting_field' => 'interested'
        ];
        $client=$this->typeSenseService->getClient();
        try{
            $client->collections->create($eventSchema);
            $output->writeln('event schema set successfully ');

        }catch (\Exception $e){
            $output->writeln('error setting schema');
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }
        return Command::SUCCESS;


    }
}