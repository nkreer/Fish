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
use IRC\User;

class PartCommand extends Command implements CommandExecutor{

    private $connection;

    public function __construct(Connection $connection){
        $this->connection = $connection;
        parent::__construct("part", $this, "Leave channels", "part <#channel1,#channel2...>");
    }

    public function onCommand(CommandInterface $command, CommandSender $sender, CommandSender $room, array $args){
        if($sender instanceof User and $sender->isOperator()){
            if(strtolower($command->getCommand() === "part")){
                $channels = explode(",", $args[1]);
                if(count($channels) >= 1){
                    foreach($channels as $channel){
                        $channel = Channel::getChannel($this->connection, $channel);
                        $this->connection->partChannel($channel);
                    }
                    $sender->sendNotice("Parted channel(s): ".implode(", ", $channels));
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            $sender->sendNotice("You don't have the permission to execute this command.");
            return true;
        }
        return false;
    }

}