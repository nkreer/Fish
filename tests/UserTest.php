<?php

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase{

    public function testUserIsOpAndIsNotIdentifiedAndHasPermission(){
        $user = new \IRC\User(new \IRC\Connection("", 6697), "!hello@example.net");
        $user->admin = true;
        $user->identified = \IRC\Authentication\AuthenticationStatus::UNIDENTIFIED;
        $this->assertEquals(false, $user->hasPermission("eating.cookies"));
    }

    public function testUserIsOpAndIsIdentifiedAndHasPermission(){
        $user = new \IRC\User(new \IRC\Connection("", 6697), "!hello@example.net");
        $user->admin = true;
        $user->identified = \IRC\Authentication\AuthenticationStatus::IDENTIFIED;
        $this->assertEquals(true, $user->hasPermission("eating.cookies"));
    }

    public function testUserDoesNotHavePermission(){
        $user = new \IRC\User(new \IRC\Connection("", 6697), "!hello@example.net");
        $this->assertEquals(false, $user->hasPermission("eating.cookies"));
    }

    public function testUserDoesHavePermission(){
        $user = new \IRC\User(new \IRC\Connection("", 6697), "!hello@example.net");
        $user->addPermission("eating.cookies");
        $this->assertEquals(true, $user->hasPermission("eating.cookies"));
    }

}