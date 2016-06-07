<?php

namespace IRC\Protocol;

use IRC\Command;
use IRC\Connection;
use IRC\Event\Channel\UserQuitEvent;
use IRC\Logger;
use IRC\User;
use IRC\Utils\JsonConfig;

class QUIT implements ProtocolCommand{

    public static function run(Command $command, Connection $connection, JsonConfig $config){
        //Tell the plugins that a user has quit
        $user = new User($connection, $command->getPrefix());
        $ev = new UserQuitEvent($user);
        $connection->getEventHandler()->callEvent($ev);
        if(!$ev->isCancelled()){
            Logger::info($user->getNick()." quit");
        }
    }

}