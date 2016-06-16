<?php

use PHPUnit\Framework\TestCase;

class IRCTest extends TestCase{

    private $irc;

    public function setUp(){
        $this->irc = new IRC\IRC(false, true);
    }

    public function testIsStartupSuccessful(){
        $this->assertInstanceOf("IRC\IRC", $this->irc);
    }

    public function testNotEncryptedConnectionIsMadeAndClosedSuccessfully(){
        $connection = new \IRC\Connection("irc.freenode.net", 6667); // I hope that freenode is reliable enough
        $this->assertTrue($this->irc->addConnection($connection));
        $this->assertTrue($this->irc->removeConnection($connection));
    }

    public function testEncryptedConnectionIsMadeAndClosedSuccessfully(){
        $connection = new \IRC\Connection("ssl://irc.freenode.net", 6697);
        $this->assertTrue($this->irc->addConnection($connection));
        $this->assertTrue($this->irc->removeConnection($connection));
    }
    
}