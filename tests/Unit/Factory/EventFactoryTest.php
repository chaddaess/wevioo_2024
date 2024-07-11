<?php

namespace App\Tests\Unit\Factory;

use App\Entity\Event;
use App\Factory\EventFactory;
use PHPUnit\Framework\TestCase;

class EventFactoryTest extends TestCase
{
    public function test_creates_event()
    {
        $factory=new EventFactory();
        $event=$factory->createEvent();
        $this->assertInstanceOf(Event::class,$event);
        $this->assertSame($event->getName(),'test-event');
    }
}