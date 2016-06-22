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

use IRC\Command\Command;
use IRC\Command\CommandExecutor;
use IRC\Command\CommandInterface;
use IRC\Command\CommandSender;
use IRC\Connection;
use IRC\IRC;
use IRC\User;

class HelpCommand extends Command implements CommandExecutor{

    private $connection;

    const COMMANDS_PER_PAGE = 3;

    public function __construct(Connection $connection){
        $this->connection = $connection;
        parent::__construct("help", $this, false, "Command help", "help <page/command>");
    }

    public function onCommand(CommandInterface $command, CommandSender $sender, CommandSender $room, array $args){
        if($sender instanceof User){
            if(isset($args[1])){
                if(is_numeric($args[1]) and $args[1] > 0){
                    $page = --$args[1];
                } elseif(is_string($args[1])) {
                    $page = strtolower($args[1]);
                } else {
                    $page = $args[1];
                }
            } else {
                $page = 0;
            }

            if(!is_numeric($page)){
                $help = $this->connection->getCommandMap()->getCommand($page);
                if($help instanceof Command){
                    $sender->sendNotice(IRC::getInstance()->getCommandPrefix().$help->getCommand()." - ".$help->getDescription()." (".$help->getUsage().")");
                } else {
                    $sender->sendNotice("Command not found.");
                }
            } else {
                $commands = $this->connection->getCommandMap()->getCommands();
                foreach($commands as $key => $help){
                    if(!$sender->hasPermission($help->getMinimumPermission()) or $help->getCommand() !== $key){
                        unset($commands[$key]);
                    }
                }
                ksort($commands);
                $list = ["Help page ".($page + 1)];
                $min = $page * self::COMMANDS_PER_PAGE;
                $max = $page * self::COMMANDS_PER_PAGE + self::COMMANDS_PER_PAGE;
                $count = 1;
                foreach($commands as $help){
                    if($count >= $min and $count < $max){
                        $list[] = IRC::getInstance()->getCommandPrefix().$help->getCommand()." - ".$help->getDescription()." (".$help->getUsage().")";
                    }
                    $count++;
                }
                if(count($list) > 1){
                    foreach($list as $entry){
                        $sender->sendNotice($entry);
                    }
                } else {
                    $sender->sendNotice("This help page is empty");
                }
            }
        }
    }

}