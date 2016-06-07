<?php

namespace IRC\Protocol;

use IRC\Channel;
use IRC\Command;
use IRC\Connection;
use IRC\Event\Channel\ChannelLeaveEvent;
use IRC\Logger;
use IRC\User;
use IRC\Utils\JsonConfig;

class PART implements ProtocolCommand{

    public static function run(Command $command, Connection $connection, JsonConfig $config){
        //Tell the plugins that a user has parted
        $channel = new Channel($connection, str_replace(":", "", $command->getArg(0)));
        $user = new User($connection, $command->getPrefix());
        $ev = new ChannelLeaveEvent($channel, $user);
        $connection->getEventHandler()->callEvent($ev);
        if(!$ev->isCancelled()){
            Logger::info($user->getNick()." left ".$channel->getName());
        }
    }

}