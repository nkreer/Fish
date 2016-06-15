<?php

use PHPUnit\Framework\TestCase;

class IRCTest extends TestCase{

    public function testIsStartupSuccessful(){
        $irc = new \IRC\IRC(false, true);
        $this->assertInstanceOf("IRC\IRC", $irc);
    }

    public function testNotEncryptedConnectionIsMadeSuccessfully(){
        $connection = new \IRC\Connection("irc.freenode.net", 6667); // I hope that freenode is reliable enough
        $irc = new \IRC\IRC(false, true);
        $result = $irc->addConnection($connection);
        $this->assertEquals(true, $result);
    }
    
    public function testEncryptedConnectionIsMadeSuccessfully(){
        $connection = new \IRC\Connection("ssl://irc.freenode.net", 6697);
        $irc = new \IRC\IRC(false, true);
        $result = $irc->addConnection($connection);
        $this->assertEquals(true, $result);
    }

}