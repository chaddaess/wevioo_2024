<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EventService
{
    private $eventCategories;
    public function __construct(private ParameterBagInterface $parameterBag){
        $this->eventCategories=$this->parameterBag->get('event.categories');
    }

    public function getEventCategories():array{
        $choices = [];
        foreach ($this->eventCategories as $category) {
            $choices[$category] = $category;
        }
        return $choices;
    }

}