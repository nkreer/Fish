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
use IRC\Event\Command\CommandEvent;
use IRC\Event\Message\MessageReceiveEvent;
use IRC\Logger;
use IRC\User;
use IRC\Utils\BashColor;
use IRC\Utils\JsonConfig;

class PRIVMSG implements ProtocolCommand{

    public static function run(Command $command, Connection $connection, JsonConfig $config){
        $user = User::getUser($connection, $command->getPrefix());
        $arg = $command->getArgs();
        if($command->getArg(0) === $connection->nickname){
            $channel = Channel::getChannel($connection, $user->getNick());
        } else {
            $channel = Channel::getChannel($connection, $arg[0]);
        }
        unset($arg[0]);
        $args = explode(":", implode(" ", $arg), 2);
        if(!in_array($args[1][0], $config->getData("command_prefix"))){ //Decide whether the message is a message or a command
            $ev = new MessageReceiveEvent($args[1], $user, $channel);
            $connection->getEventHandler()->callEvent($ev);
            if(!$ev->isCancelled()){
                Logger::info(BashColor::HIGHLIGHT.$ev->getChannel()->getName()." ".$ev->getUser()->getNick().":".BashColor::REMOVE." ".$ev->getMessage()); //Display the message to the console
            }
        } else {
            $args[1] = substr($args[1], 1);
            $args[1] = explode(" ", $args[1]);
            $cmd = $args[1][0];
            unset($args[1][0]);
            Logger::info(BashColor::CYAN.$user->getNick()." > ".$cmd." ".implode(" ", $args[1]));
            $ev = new CommandEvent($cmd, $args[1], $channel, $user);
            $connection->getEventHandler()->callEvent($ev);
        }
    }

}