<?php

use PHPUnit\Framework\TestCase;

class EventHandlerTest extends TestCase{

    private $handler;

    public function setUp(){
        $this->handler = new \IRC\Event\EventHandler();
    }

    public function testEventHandling(){
        $this->handler->registerEvents(new TestListener());
        $this->handler->callEvent(new \IRC\Event\Ping\PingEvent());
    }

}

class TestListener extends TestCase implements \IRC\Event\Listener{
    
    public function onPingEvent(\IRC\Event\Ping\PingEvent $event){
        $this->assertInstanceOf("\IRC\Event\Ping\PingEvent", $event);
    }

}