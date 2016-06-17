<?php

use PHPUnit\Framework\TestCase;

class KICKTest extends TestCase{

    private $connection;
    private $eventHandler;

    public function setUp(){
        $this->connection = new \IRC\Connection("", 6667);
        $this->eventHandler = new \IRC\Event\EventHandler();
    }

    public function testIncomingMessageIsParsedCorrectly(){
        $this->eventHandler->registerEvents(new TestKick());
        $this->connection->check("fish~!someone@example.com KICK #fish-irc fish");
    }

}

class TestKick extends TestCase implements \IRC\Event\Listener{

    public function onKickEvent(\IRC\Event\Kick\KickEvent $event){
        $this->assertEquals("fish", $event->getKicker()->getNick());
        $this->assertEquals("fish", $event->getUser()->getNick());
        $this->assertEquals("example.com", $event->getUser()->getAddress());
        $this->assertEquals("#fish-irc", $event->getChannel()->getName());
    }

}