<?php

namespace IRC\Protocol;

use IRC\Command;
use IRC\Connection;
use IRC\Event\Ping\PingEvent;
use IRC\Utils\JsonConfig;

class PING implements ProtocolCommand{

    public static function run(Command $command, Connection $connection, JsonConfig $config){
        $ev = new PingEvent();
        $connection->getEventHandler()->callEvent($ev);
        if(!$ev->isCancelled()){
            $connection->sendData("PONG ".$command->getArgs()[0]); //Reply to Pings
        }
    }

}