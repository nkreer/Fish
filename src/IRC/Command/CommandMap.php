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

use IRC\Connection;
use IRC\Event\Command\CommandRegisterEvent;
use IRC\Event\Command\CommandUnregisterEvent;
use IRC\Plugin\Plugin;

class CommandMap{

    private $commands = [];
    private $plugins = [];
    private $connection;

    public function __construct(Connection $connection){
        $this->connection = $connection;
    }

    /**
     * @return Command[]
     */
    public function getCommands() : array{
        return $this->commands;
    }

    /**
     * @param CommandInterface $command
     * @param Plugin|null $plugin
     * @return bool
     */
    public function reloadCommand(CommandInterface $command, Plugin $plugin = null) : bool{
        if($this->hasCommand($command->getCommand())){
            $this->unregisterCommand($command, $plugin);
            $this->registerCommand($command, $plugin);
            return true;
        }
        return false;
    }

    /**
     * @param String $label
     * @return bool
     */
    public function hasCommand($label) : bool{
        return isset($this->commands[$label]);
    }

    /**
     * @param CommandInterface $command
     * @param Plugin|null $plugin
     * @return bool
     */
    public function unregisterCommand(CommandInterface $command, Plugin $plugin = null) : bool{
        if($this->hasCommand($command->getCommand())){
            $ev = new CommandUnregisterEvent($command);
            $this->connection->getEventHandler()->callEvent($ev);
            if(!$ev->isCancelled()){
                unset($this->commands[$command->getCommand()]);
                foreach($command->getAliases() as $alias){
                    if($this->hasCommand($alias)){
                        unset($this->commands[$alias]);
                    }
                }
                if($plugin instanceof Plugin){
                    unset($this->plugins[$plugin->name][$command->getCommand()]);
                }
                return true;
            }
        }
        return false;
    }

    /**
     * @param CommandInterface $command
     * @param Plugin|null $plugin
     * @return bool
     */
    public function registerCommand(CommandInterface $command, Plugin $plugin = null) : bool{
        if(!$this->hasCommand($command->getCommand())){
            $ev = new CommandRegisterEvent($command);
            $this->connection->getEventHandler()->callEvent($ev);
            if(!$ev->isCancelled()){
                $this->commands[$command->getCommand()] = $command;
                foreach($command->getAliases() as $alias){
                    if(!$this->hasCommand($alias)){
                        $this->commands[$alias] = $command;
                    }
                }
                if($plugin instanceof Plugin){
                    $this->plugins[$plugin->name][$command->getCommand()] = $command;
                }
                return true;
            }
        }
        return false;
    }

    /**
     * @param Plugin $plugin
     * @return bool
     */
    public function unregisterPlugin(Plugin $plugin) : bool{
        if($this->hasPlugin($plugin)){
            foreach($this->plugins[$plugin->name] as $label => $command){
                $this->unregisterCommand($command, $plugin);
            }
            unset($this->plugins[$plugin->name]);
            return true;
        }
        return false;
    }

    /**
     * @param Plugin $plugin
     * @return bool
     */
    public function hasPlugin(Plugin $plugin) : bool{
        return isset($this->plugins[$plugin->name]);
    }

    /**
     * @param String $label
     * @return bool|Command
     */
    public function getCommand(String $label){
        if($this->hasCommand($label)){
            return $this->commands[$label];
        }
        return false;
    }

}