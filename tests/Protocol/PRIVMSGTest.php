<?php

use PHPUnit\Framework\TestCase;

class PRIVMSGTest extends TestCase{

    private $connection;
    private $eventHandler;

    public function setUp(){
        $this->connection = new \IRC\Connection("", 6667);
        $this->eventHandler = new \IRC\Event\EventHandler();
    }

    public function testIncomingMessageIsParsedCorrectly(){
        $this->eventHandler->registerEvents(new TestPrivmsg());
        $this->connection->check("fish~!someone@example.com PRIVMSG #fish-irc :Hey!");
    }

}

class TestPrivmsg extends TestCase implements \IRC\Event\Listener{

    public function onMessageReceiveEvent(\IRC\Event\Message\MessageReceiveEvent $event){
        $this->assertEquals("Hey!", $event->getMessage());
        $this->assertEquals("fish", $event->getUser()->getNick());
        $this->assertEquals("example.com", $event->getUser()->getAddress());
        $this->assertEquals("#fish-irc", $event->getChannel()->getName());
    }

}