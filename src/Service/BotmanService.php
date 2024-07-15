<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class BotmanService
{
    private $application;
    public function __construct( private  readonly TypeSenseService $typeSenseService,KernelInterface $kernel, private  readonly Security $security){
        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);
    }
    public function handleUnknown():string{
        return "Sorry, I was not trained to answer that"."<br>".
        "Here's a list of things I can do:"."<br>".
        "👉 set your event schema (/set)  "."<br>".
        "👉 load your local events to the cloud (/load) "."<br>".
        "👉search for events by keywords (/search)"."<br>";
    }
    public  function handleCreateSchema(): string
    {
        $input = new ArrayInput([
            'command' => 'typesense:set-event-schema',
        ]);
        $output = new BufferedOutput();
        $this->application->run($input, $output);
        return $output->fetch();
    }


    public  function handleLoadData(): string
    {
        $input = new ArrayInput([
            'command' => 'typesense:load-event-data',
        ]);
        $output = new BufferedOutput();
        $this->application->run($input, $output);
        return $output->fetch();
    }


}