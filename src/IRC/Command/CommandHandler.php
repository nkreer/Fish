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

namespace IRC\Command;

use IRC\Channel;
use IRC\Connection;
use IRC\Event\Command\CommandSendUsageTextEvent;
use IRC\User;

class CommandHandler{

    private $connection;
    private $timers;

    public function __construct(Connection $connection){
        $this->connection = $connection;
    }

    public function handleCommand($cmd, User $user, Channel $channel, $args){
        $cmd = $this->connection->getCommandMap()->getCommand($cmd);
        if($cmd instanceof CommandInterface){
            $result = $this->connection->getPluginManager()->command($cmd, $args[1], $user, $channel);
            if($result === false and $cmd->getUsage() !== ""){
                $ev = new CommandSendUsageTextEvent($cmd, $user, $channel, $args[1]);
                if(!$ev->isCancelled()){
                    $channel->sendNotice("Usage: ".$cmd->getUsage());
                }
            }
        }
    }

}