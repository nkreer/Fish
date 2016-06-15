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
use IRC\Event\Channel\ChannelLeaveEvent;
use IRC\Logger;
use IRC\User;
use IRC\Utils\JsonConfig;

/**
 * Someone left a channel
 * Class PART
 * @package IRC\Protocol
 */
class PART implements ProtocolCommand{

    public static function run(Command $command, Connection $connection, JsonConfig $config){
        //Tell the plugins that a user has parted
        $channel = Channel::getChannel($connection, str_replace(":", "", $command->getArg(0)));
        $user = User::getUser($connection, $command->getPrefix());
        if($user instanceof User){
            $ev = new ChannelLeaveEvent($channel, $user);
            $connection->getEventHandler()->callEvent($ev);
            if(!$ev->isCancelled()){
                Logger::info($user->getNick()." left ".$channel->getName());
                User::removeUser($connection, $user->getHostmask()); //Remove the user, don't care if they are in other channels the bot is in
            }
        }
    }

}