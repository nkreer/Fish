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
use IRC\Event\Topic\TopicReceiveEvent;
use IRC\Utils\JsonConfig;

class _332 implements ProtocolCommand{

    public static function run(Command $command, Connection $connection, JsonConfig $config){
        $args = $command->getArgs();
        $channel = Channel::getChannel($connection, $args[1]);
        for($a = 0; $a <= 1; $a++) unset($args[$a]);
        $topic = explode(":", implode(" ", $args), 2)[1];
        $channel->topic = $topic;
        $ev = new TopicReceiveEvent($topic, $channel);
        $connection->getEventHandler()->callEvent($ev);
    }

}