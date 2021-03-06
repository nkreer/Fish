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

namespace IRC\Plugin;

use Composer\Autoload\ClassLoader;
use IRC\Command\Command;
use IRC\Command\CommandInterface;
use IRC\Command\CommandSender;
use IRC\Connection;
use IRC\Logger;
use IRC\Utils\BashColor;

class Plugin{

    public $name;
    public $description;
    public $apiVersion;
    public $version;
    public $author;
    public $commands = [];
    public $main;

    public $reflectionClass;

    /**
     * @var PluginBase
     */
    public $class;

    public function __construct(String $name, array $json, Connection $connection, $pharPlugin = true){
        if(file_exists(($pharPlugin ? "phar://" : "")."plugins".DIRECTORY_SEPARATOR.$name.($pharPlugin ? ".phar" : "").DIRECTORY_SEPARATOR."plugin.json")){
            Logger::info(BashColor::GREEN."Loading plugin ".BashColor::BLUE.$name);

            $this->name = $json["name"];
            $this->description = $json["description"];
            $this->apiVersion = $json["api"];
            $this->version = $json["version"];
            $this->author = $json["author"];
            $this->main = $json["main"];

            //Instantiating plugins
            $info = new \SplFileInfo(($pharPlugin ? "phar://" : "")."plugins".DIRECTORY_SEPARATOR.$name.($pharPlugin ? ".phar" : "").DIRECTORY_SEPARATOR.$this->main);
            $class = new \ReflectionClass("\\".$name."\\".$info->getBasename(".php")); //Taking care of using the correct namespace
            $this->class = $class->newInstanceWithoutConstructor();
            $this->reflectionClass = $class;
            $this->class->connection = $connection;
            $this->class->plugin = $this;

            //Registering commands
            if(isset($json["commands"])){
                $this->commands = $json["commands"];
                foreach($this->commands as $command => $settings){
                    // Set description
                    $description = (!empty($settings["description"]) ? $settings["description"] : "");
                    // Set usage help
                    $usage = (!empty($settings["usage"]) ? $settings["usage"] : $command);
                    // Set required permission
                    $permission = (!empty($settings["permission"]) ? $settings["permission"] : false); //Default permission is false
                    $command = new Command($command, $this->class, $permission, $description, $usage);
                    // Add aliases
                    if(isset($settings["aliases"]) && is_array($settings["aliases"])){
                        foreach($settings["aliases"] as $alias){
                            $command->addAlias($alias);
                        }
                    }
                    $connection->getCommandMap()->registerCommand($command, $this);
                }
            }
        }
    }

    public function load(){
        if($this->reflectionClass->hasMethod("onLoad")){
            $this->class->onLoad(); //Call the onLoad method
        }
    }

    public function command(CommandInterface $command, CommandSender $sender, CommandSender $room, array $args){
        if($this->reflectionClass->hasMethod("onCommand")){
            $return = $this->class->onCommand($command, $sender, $args);
            return ($return === null ? false : $return);
        }
        return false;
    }

    public function unload(){
        if($this->reflectionClass->hasMethod("onDisable")){
            $this->class->onDisable();
        } elseif($this->reflectionClass->hasMethod("onUnload")){
            $this->class->onUnload();
        }
    }

}