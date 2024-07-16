<?php

namespace App\Service;

use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class BotmanService
{
    private $application;
    public function __construct( private readonly TypeSenseService $typeSenseService,KernelInterface $kernel){
        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);
    }

    public function handleUnknown():string{
        return "Sorry, I was not trained to answer that"."<br>".
        "Here's a list of things I can do:"."<br>".
        "👉 set your event schema (<b>/set</b> command)  "."<br>".
        "👉 load your local events to the cloud (<b>/load</b> command) "."<br>".
        "👉search for events by keywords (<b>/search</b> command)"."<br>";
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
    public function handleSearch($keywords):string{
        try{
            $events=$this->typeSenseService->search('events',$keywords,[]);

        }catch (Exception $e){
            return('error searching: '.$e->getMessage());
        }
        if(!$events){
            return "Sorry, I couldn't find any event corresponding to this search";
        }else{
            $name=$events[0]["document"]["name"];
            $id=$events[0]["document"]["id"];
            $path=$_ENV['HOSTNAME']."/event/$id";
            $htmlOutput= "<a href=\"$path\">$name</a><br>";
            if($keywords=='*'){
                return "Since you didn't provide any keywords, here's the most popular event at the moment 🪩"."<br>".$htmlOutput;
            }
            return "Sure ! Here is the top event corresponding to your search 🕺: "."<br>".$htmlOutput;

        }
    }




}