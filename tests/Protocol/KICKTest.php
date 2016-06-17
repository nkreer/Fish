<?php

use PHPUnit\Framework\TestCase;

class KICKTest extends TestCase{

    public $irc;
    public $connection;

    public function setUp(){
        $this->irc = new IRC\IRC(false, true);
        $this->connection = new \IRC\Connection("", 6697);
    }

    public function testIncomingMessageIsParsedCorrectly(){
        $result = $this->connection->check("fish~!someone@example.com KICK #fish-irc fish");
        $this->assertInstanceOf("\IRC\Command", $result);
    }

}