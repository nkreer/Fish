<?php

use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase{

    public function testHasManagementCommands(){
        new \IRC\IRC(false, true);
        $connection = new \IRC\Connection("", 6697);
        $this->assertInstanceOf("IRC\Management\HelpCommand", $connection->getCommandMap()->getCommand("help"));
        $this->assertInstanceOf("IRC\Management\JoinCommand", $connection->getCommandMap()->getCommand("join"));
        $this->assertInstanceOf("IRC\Management\PartCommand", $connection->getCommandMap()->getCommand("part"));
    }

}