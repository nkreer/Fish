<?php

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase{

    private $user;

    public function setUp(){
        $this->user = new IRC\User(new IRC\Connection("", 6697), "test~!hello@example.net");
    }

    public function testUserIsOpAndIsNotIdentifiedAndHasPermission(){
        $this->user->admin = true;
        $this->user->identified = \IRC\Authentication\AuthenticationStatus::UNIDENTIFIED;
        $this->assertFalse($this->user->hasPermission("eating.cookies"));
    }

    public function testUserIsOpAndIsIdentifiedAndHasPermission(){
        $this->user->admin = true;
        $this->user->identified = \IRC\Authentication\AuthenticationStatus::IDENTIFIED;
        $this->assertTrue($this->user->hasPermission("eating.cookies"));
    }

    public function testUserDoesNotHavePermission(){
        $this->assertFalse($this->user->hasPermission("eating.cookies"));
    }

    public function testUserDoesHavePermission(){
        $this->user->addPermission("eating.cookies");
        $this->assertTrue($this->user->hasPermission("eating.cookies"));
    }

}