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
use IRC\Event\Channel\BotJoinChannelEvent;
use IRC\Event\Channel\JoinChannelEvent;
use IRC\Logger;
use IRC\User;
use IRC\Utils\JsonConfig;

/**
 * Someone joins a channel
 * Class JOIN
 * @package IRC\Protocol
 */
class JOIN implements ProtocolCommand{

    public static function run(Command $command, Connection $connection, JsonConfig $config){
        //Tell the plugins that a user has joined
        $channel = Channel::getChannel($connection, str_replace(":", "", $command->getArg(0)));
        $user = User::getUser($connection, $command->getPrefix());
        if($user->getNick() === $connection->getNick()){
            $ev = new BotJoinChannelEvent($channel, $user);
        } else {
            $ev = new JoinChannelEvent($channel, $user);
        }
        $connection->getEventHandler()->callEvent($ev);
        if(!$ev->isCancelled()){
            Logger::info($user->getNick()." joined ".$channel->getName());
            if($ev instanceof BotJoinChannelEvent){
                $connection->addChannel($channel); // Add the channel
            }
        }
    }

}