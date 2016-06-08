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
use IRC\Event\Mode\ChannelModeChangeEvent;
use IRC\Event\Mode\MyModesChangeEvent;
use IRC\Event\Mode\UserModeChangeEvent;
use IRC\User;
use IRC\Utils\JsonConfig;

class MODE implements ProtocolCommand{

    public static function run(Command $command, Connection $connection, JsonConfig $config){
        $user = User::getUser($connection, $command->getPrefix());
        $channel = Channel::getChannel($connection, $command->getArg(0));
        $mode = $command->getArg(1);
        if($channel->getName() === $connection->getNick()){
            $ev = new MyModesChangeEvent($mode, $user, $channel);
            $connection->getEventHandler()->callEvent($ev);
        } elseif(empty($command->getArg(2))){
            $ev = new ChannelModeChangeEvent($mode, $user, $channel);
            $connection->getEventHandler()->callEvent($ev);
        } else {
            $nick = $command->getArg(2);
            $ev = new UserModeChangeEvent($mode, $user, $channel, $nick);
            $connection->getEventHandler()->callEvent($ev);
        }
    }

}