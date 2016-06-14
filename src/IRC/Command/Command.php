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

class Command implements CommandInterface{

    private $command;
    private $permission;
    private $description;
    private $usage;
    private $aliases = [];

    private $executor;

    public function __construct(String $command, CommandExecutor $executor, $minimumPermission = true,
                                String $description = "", String $usage = ""){
        $this->description = $description;
        $this->usage = $usage;
        $this->command = $command;
        $this->executor = $executor;
        $this->permission = $minimumPermission;
    }

    public function getMinimumPermission(){
        return $this->permission;
    }

    public function getExecutor() : CommandExecutor{
        return $this->executor;
    }

    public function setExecutor(CommandExecutor $executor){
        $this->executor = $executor;
    }

    public function getCommand() : String{
        return $this->command;
    }

    public function getDescription() : String{
        return $this->description;
    }

    public function setDescription(String $description){
        $this->description = $description;
    }

    public function getUsage() : String{
        return $this->usage;
    }

    public function setUsage(String $usage){
        $this->usage = $usage;
    }

    public function getAliases() : array{
        return $this->aliases;
    }

    public function addAlias(String $alias){
        $this->aliases[$alias] = $alias;
    }

    public function removeAlias(String $alias){
        if(isset($this->aliases[$alias])){
            unset($this->aliases[$alias]);
        }
    }

}