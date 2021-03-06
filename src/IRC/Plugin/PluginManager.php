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
use IRC\Command;
use IRC\Command\CommandSender;
use IRC\Connection;
use IRC\Event\Plugin\PluginLoadEvent;
use IRC\Event\Plugin\PluginUnloadEvent;
use IRC\IRC;
use IRC\Logger;
use IRC\Utils\BashColor;
use IRC\Utils\JsonConfig;

class PluginManager{

    private $plugins = [];
    private $connection;
    private $classLoader;

    public function __construct(Connection $connection){
        $this->connection = $connection;
        $this->classLoader = new ClassLoader();
        $this->classLoader->register();
    }

    /**
     * @return ClassLoader
     */
    public function getClassLoader(){
        return $this->classLoader;
    }

    /**
     * @return Connection
     */
    public function getConnection(){
        return $this->connection;
    }

    public function command(Command\CommandInterface $command, array $args, CommandSender $sender, CommandSender $room){
        $executor = $command->getExecutor();
        if($executor instanceof Plugin){
            return $this->getPlugin($executor->name)->command($command, $sender, $room, $args);
        } else {
            $reflection = new \ReflectionClass($executor);
            if($reflection->hasMethod("onCommand")){
                return $executor->onCommand($command, $sender, $room, $args);
            }
        }
        return false;
    }

    /**
     * @return Plugin[]
     */
    public function getPlugins() : array{
        return $this->plugins;
    }

    /**
     * @param $name
     * @return Plugin|NULL
     */
    public function getPlugin(String $name){
        return $this->plugins[$name];
    }

    public function loadAll(){
        foreach(scandir("plugins/") as $element){
            if(is_file("plugins/".$element)){
                // Make sure we're loading a phar plugin
                if(stripos($element, ".phar") !== false){
                    $this->loadPlugin($element, true);
                }
            }
        }
    }

    /**
     * @param String $name
     * @param bool $pharPlugin
     * @param bool $force
     * @return bool|int
     */
    public function loadPlugin(String $name, bool $pharPlugin = true, bool $force = false){
        if(file_exists(($pharPlugin ? "phar://" : "")."plugins".DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR."plugin.json") && !$this->hasPlugin($name)){
            $json = new JsonConfig();
            $json->loadFile(($pharPlugin ? "phar://" : "")."plugins".DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR."plugin.json");
            $json = $json->getConfig();

            if(!isset($json["load"]) or $json["load"] !== false or $force === true){
                if($json["api"] != IRC::VERSION){
                    Logger::info(BashColor::YELLOW."Plugin ".$name." was developed for Fish version ".$json["api"].", while you are running version ".IRC::VERSION."! It might not be compatible!");
                }

                $success = true;
                if(isset($json["depend"])){
                    foreach($json["depend"] as $dependency){
                        Logger::info(BashColor::YELLOW."Loading dependency ".$dependency);
                        $success = $this->loadPlugin($dependency, true);
                        if($success === false){
                            Logger::info(BashColor::RED."Couldn't find dependency ".$dependency);
                            break;
                        }
                    }
                }

                if($success !== false){
                    $this->getClassLoader()->addPsr4(basename($name, ".phar")."\\", ($pharPlugin ? "phar://" : "")."plugins".DIRECTORY_SEPARATOR.$name);

                    $plugin = new Plugin(basename($name, ".phar"), $json, $this->getConnection(), $pharPlugin);

                    $ev = new PluginLoadEvent($plugin);
                    $this->getConnection()->getEventHandler()->callEvent($ev);
                    if(!$ev->isCancelled()){
                        $key = count($this->plugins);
                        $this->plugins[$plugin->name] = $plugin;
                        $plugin->load();
                        return $key;
                    } else {
                        unset($plugin); // Don't keep it loaded
                    }
                }
            }
        }
        return false;
    }

    /**
     * Unload all plugins
     */
    public function unloadAll(){
        foreach($this->plugins as $plugin){
            $this->unloadPlugin($plugin);
        }
    }

    /**
     * @param Plugin $plugin
     * @return bool
     */
    public function unloadPlugin(Plugin $plugin) : bool{
        if($this->hasPlugin($plugin->name)){
            $ev = new PluginUnloadEvent($plugin);
            $this->getConnection()->getEventHandler()->callEvent($ev);
            if(!$ev->isCancelled()){
                Logger::info(BashColor::RED."Unloading plugin ".BashColor::BLUE.$plugin->name);
                $plugin->unload();
                unset($this->plugins[$plugin->name]);
                $this->getConnection()->getCommandMap()->unregisterPlugin($plugin);
                $this->getConnection()->getEventHandler()->unregisterPlugin($plugin);
                $this->getConnection()->getScheduler()->cancelPluginTasks($plugin->name);
                unset($plugin);
                return true;
            }
        }
        return false;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasPlugin($name) : bool{
        return isset($this->plugins[$name]);
    }

    /**
     * @param Plugin $plugin
     */
    public function reloadPlugin(Plugin $plugin){
        $this->unloadPlugin($plugin);
        $this->loadPlugin($plugin->name, true);
    }

    /**
     * Reload all plugins
     */
    public function reloadAll(){
        $this->unloadAll();
        $this->loadAll();
    }

}