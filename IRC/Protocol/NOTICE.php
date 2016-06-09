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
use IRC\Event\Notice\NoticeReceiveEvent;
use IRC\Logger;
use IRC\User;
use IRC\Utils\BashColor;
use IRC\Utils\JsonConfig;

/**
 * Someone sends a notice
 * Class NOTICE
 * @package IRC\Protocol
 */
class NOTICE implements ProtocolCommand{
    
    public static function run(Command $command, Connection $connection, JsonConfig $config){
        $user = User::getUser($connection, $command->getPrefix());
        $arg = $command->getArgs();
        if($command->getArg(0) === $connection->nickname){
            $channel = Channel::getChannel($connection, $user->getNick());
        } else {
            $channel = Channel::getChannel($connection, $arg[0]);
        }
        unset($arg[0]);
        $ev = new NoticeReceiveEvent(implode(" ", $arg), $user, $channel);
        $connection->getEventHandler()->callEvent($ev);
        if(!$ev->isCancelled()){
            Logger::info(BashColor::HIGHLIGHT.$ev->getUser()->getNick().": ".$ev->getNotice().BashColor::REMOVE); //Display the notice to the console
        }
    }

}