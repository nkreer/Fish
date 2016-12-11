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
use IRC\Plugin\Plugin;

class PluginReloadCommand extends Command implements CommandExecutor{

    private $connection;

    public function __construct(Connection $connection){
        $this->connection = $connection;
        parent::__construct("reloadPlugin", $this, "fish.management.reloadplugin", "Reload plugin", "reloadplugin <plugin>");
        $this->addAlias("reloadMod");
        $this->addAlias("reloadModule");
    }

    public function onCommand(CommandInterface $command, CommandSender $sender, CommandSender $room, array $args){
        if(!empty($args[1])){
            if($plugin = $this->connection->getPluginManager()->getPlugin($args[1]) and $plugin instanceof Plugin){
                if($this->connection->getPluginManager()->unloadPlugin($plugin)){
                    if($this->connection->getPluginManager()->loadPlugin($plugin->name)){
                        $sender->sendNotice("Plugin ".$args[1]." was reloaded successfully.");
                    } else {
                        $sender->sendNotice("Could not reload reload the plugin because of an error.");
                    }
                } else {
                    $sender->sendNotice("Plugin ".$args[1]." could not be unloaded.");
                }
            } else {
                $sender->sendNotice("That plugin isn't loaded.");
            }
            return true;
        }
        return false;
    }

}