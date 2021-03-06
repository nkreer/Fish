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

use IRC\Command;
use IRC\Connection;
use IRC\Event\Nick\UserChangeNickEvent;
use IRC\Logger;
use IRC\User;
use IRC\Utils\JsonConfig;

/**
 * Someone changes nick
 * Class NICK
 * @package IRC\Protocol
 */
class NICK implements ProtocolCommand{

    public static function run(Command $command, Connection $connection, JsonConfig $config){
        $user = User::getUser($connection, $command->getPrefix());
        $new = str_replace(":", "", $command->getArg(0));
        $ev = new UserChangeNickEvent($user->getNick(), $new);
        $connection->getEventHandler()->callEvent($ev);
        if(!$ev->isCancelled()){
            Logger::info($user->getNick()." is now known as ".$new);
            if($user->getNick() === $connection->getNick()){
                // Bot changed name
                $connection->nickname = $new;
            } else {
                // Make sure that we can still message them from the old object
                $user->nick = $new;
            }
            User::removeUser($connection, $user->getHostmask()); //Remove old authentication status, mainly
        }
    }

}