<?php

use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase{
    
    private $connection;
    
    public function setUp(){
        new \IRC\IRC(false, true);
        $this->connection = new IRC\Connection("", 6697);
    }

    public function testHasManagementCommands(){
        $this->assertInstanceOf("IRC\Management\HelpCommand", $this->connection->getCommandMap()->getCommand("help"));
        $this->assertInstanceOf("IRC\Management\JoinCommand", $this->connection->getCommandMap()->getCommand("join"));
        $this->assertInstanceOf("IRC\Management\PartCommand", $this->connection->getCommandMap()->getCommand("part"));
    }

}