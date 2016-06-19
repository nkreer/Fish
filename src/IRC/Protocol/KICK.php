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
use IRC\Event\Kick\KickEvent;
use IRC\Logger;
use IRC\User;
use IRC\Utils\JsonConfig;

/**
 * Someone gets kicked from
 * Class KICK
 * @package IRC\Protocol
 */
class KICK implements ProtocolCommand{

    public static function run(Command $command, Connection $connection, JsonConfig $config){
        //Tell the plugins that a user was kicked
        $channel = Channel::getChannel($connection, str_replace(":", "", $command->getArg(0)));
        $kicker = User::getUser($connection, $command->getPrefix());
        $user = $command->getArg(1);
        $ev = new KickEvent($user, $channel, $kicker);
        $connection->getEventHandler()->callEvent($ev);
        if(!$ev->isCancelled()){
            Logger::info($user." was kicked from ".$channel->getName());
        }
        $user = User::getUserByNick($connection, $user);
        if($user instanceof User){
            User::removeUser($connection, $user->getHostmask()); //Don't care about other channels
        }
    }

}