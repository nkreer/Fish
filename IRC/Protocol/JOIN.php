<?php

namespace IRC\Protocol;

use IRC\Channel;
use IRC\Command;
use IRC\Connection;
use IRC\Event\Channel\JoinChannelEvent;
use IRC\Logger;
use IRC\User;
use IRC\Utils\JsonConfig;

class JOIN implements ProtocolCommand{

    public static function run(Command $command, Connection $connection, JsonConfig $config){
        //Tell the plugins that a user has joined
        $channel = new Channel($connection, str_replace(":", "", $command->getArg(0)));
        $user = new User($connection, $command->getPrefix());
        $ev = new JoinChannelEvent($channel, $user);
        $connection->getEventHandler()->callEvent($ev);
        if(!$ev->isCancelled()){
            Logger::info($user->getNick()." joined ".$channel->getName());
        }
    }

}