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

namespace IRC\Management;

use IRC\Channel;
use IRC\Command\Command;
use IRC\Command\CommandExecutor;
use IRC\Command\CommandInterface;
use IRC\Command\CommandSender;
use IRC\Connection;

class JoinCommand extends Command implements CommandExecutor{

    private $connection;

    public function __construct(Connection $connection){
        $this->connection = $connection;
        parent::__construct("join", $this, "fish.management.join", "Join channels", "join <#channel1,#channel2...>");
    }

    public function onCommand(CommandInterface $command, CommandSender $sender, CommandSender $room, array $args){
        $channels = explode(",", $args[1]);
        if(!empty($channels)){
            foreach($channels as $channel){
                $channel = Channel::getChannel($this->connection, $channel);
                $this->connection->joinChannel($channel);
            }
            $sender->sendNotice("Joined channel(s): ".implode(", ", $channels));
            return true;
        }
        return false;
    }

}