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

class PluginLoadCommand extends Command implements CommandExecutor{

    private $connection;

    public function __construct(Connection $connection){
        $this->connection = $connection;
        parent::__construct("loadPlugin", $this, "fish.management.loadplugin", "Load plugin", "loadplugin <plugin>");
        $this->addAlias("lp");
        $this->addAlias("loadMod");
        $this->addAlias("loadModule");
    }
    
    public function onCommand(CommandInterface $command, CommandSender $sender, CommandSender $room, array $args){
        if(!empty($args[1])){
            if($this->connection->getPluginManager()->loadPlugin($args[1].".phar", true) !== false){
                $sender->sendNotice("Plugin ".$args[1]." was loaded successfully.");
            } else {
                $sender->sendNotice("Plugin ".$args[1]." could not be loaded.");
            }
            return true;
        }
        return false;
    }

}