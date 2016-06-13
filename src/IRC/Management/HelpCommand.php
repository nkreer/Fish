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

class HelpCommand extends Command implements CommandExecutor{

    private $connection;

    public function __construct(Connection $connection){
        $this->connection = $connection;
        parent::__construct("help", $this, "Command help", "help <page/command>");
    }

    public function onCommand(CommandInterface $command, CommandSender $sender, CommandSender $room, array $args){
        if(strtolower($command->getCommand()) === "help"){
            if(is_numeric($args[1]) or empty($args[1])){
                $page = $args[1];
                if(empty($page)){
                    $page = 1;
                }
                $commands = $this->connection->getCommandMap()->getCommands();
                $commands = array_values($commands);
                $sender->sendNotice("Help page ".$page);
                for($p = ($page - 1); $p <= ($page + 4); $p++){
                    if(isset($commands[$p]) and $commands[$p] instanceof CommandInterface){
                        $sender->sendNotice($commands[$p]->getCommand().": ".$commands[$p]->getDescription());
                    }
                }
            } elseif(is_string($args[1])) {
                $cmd = $this->connection->getCommandMap()->getCommand($args[1]);
                if($cmd instanceof CommandInterface){
                    $sender->sendNotice($cmd->getCommand().": ".$cmd->getDescription()." (Usage: ".$cmd->getUsage().")");
                } else {
                    $sender->sendNotice("Command not found.");
                }
            }
        }
    }

}