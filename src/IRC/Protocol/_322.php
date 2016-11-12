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
use IRC\Event\Channel\ChannelListEvent;
use IRC\Utils\JsonConfig;

/**
 * LIST info
 * Class _322
 * @package IRC\Protocol
 */
class _322 implements ProtocolCommand{

    public static function run(Command $command, Connection $connection, JsonConfig $config){
        $channelName = $command->getArg(1);
        $userCount = $command->getArg(2);
        $args = $command->getArgs();
        $topic = explode(":", implode(" ", $args), 2)[1];
        $event = new ChannelListEvent($channelName, $userCount, $topic);
        $connection->getEventHandler()->callEvent($event);
    }

}