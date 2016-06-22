<?php

/*
 *
 * Fish - IRC Bot
 * Copyright (C) 2016 Niklas Kreer
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace IRC\Protocol;

use IRC\Channel;
use IRC\Command;
use IRC\Connection;
use IRC\Event\Connection\ConnectionFinishedEvent;
use IRC\IRC;
use IRC\Utils\JsonConfig;

/**
 * End of MOTD
 * Class _376
 * @package IRC\Protocol
 */
class _376 implements ProtocolCommand{

    public static function run(Command $command, Connection $connection, JsonConfig $config){
        $ev = new ConnectionFinishedEvent($connection);
        $connection->getEventHandler()->callEvent($ev);
        if(!$ev->isCancelled()){
            $config = IRC::getInstance()->getConfig()->getData("connections");
            if(!empty($config[$connection->getAddress()]["nickserv"])){
                $connection->getNickServ()->identify($config[$connection->getAddress()]["nickserv"]); //Identify with NickServ
            }
            if(!empty($config[$connection->getAddress()]["channels"])){
                $channels = $config[$connection->getAddress()]["channels"];
                foreach($channels as $channel){
                    $connection->joinChannel(Channel::getChannel($connection, $channel)); //Join these channels
                }
            }
        }
    }

}