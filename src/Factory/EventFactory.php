<?php

namespace App\Factory;


namespace App\Factory;

use App\Entity\Event;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EventFactory
{
    public function __construct()
    {

    }

    public function createEvent():Event
    {
        $event = new Event();
        $event->setName('test-event');
        $event->setCreator('test-admin-1');
        $event->setLocation(['70.4','20.8']);
        $event->setAddress('testland');
        $event->setAttending(0);
        $event->setInterested(0);
        $event->setCategory('Entertainment');
        $event->setDate(new \DateTime());
        $event->setComments('none');
        $event->setPicture('np-image.png');
        return $event;

    }


}