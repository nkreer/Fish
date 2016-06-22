<?php

use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase{

    /**
     * @var IRC\Connection
     */
    private $connection;
    
    public function setUp(){
        new \IRC\IRC(false, true);
        $this->connection = new IRC\Connection("", 6697);
    }

    public function testParsing(){
        $command = $this->connection->check("hello!~test@example.net PRIVMSG #fish-irc :test");
        $this->assertInstanceOf("IRC\Command", $command);
    }
    
}