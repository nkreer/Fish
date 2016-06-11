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

    public function __construct(Connection $connection){
        $this->connection = $connection;
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
            if(is_dir("plugins/".$element)){
                $this->loadPlugin($element);
            }
        }
    }

    /**
     * @param $name
     * @return bool|int
     */
    public function loadPlugin(String $name, bool $force = false){
        if(file_exists("plugins/".$name."/plugin.json")){
            $json = new JsonConfig();
            $json->loadFile("plugins/".$name."/plugin.json");
            $json = $json->getConfig();

            if(isset($json["load"]) and $json["load"] !== false or $force == true){
                if($json["api"] != IRC::API_VERSION){
                    Logger::info(BashColor::YELLOW."Plugin ".$name." is not supported by this version of Fish.");
                }

                $plugin = new Plugin($name, $json, $this->connection);

                $ev = new PluginLoadEvent($plugin);
                $this->connection->getEventHandler()->callEvent($ev);
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
        return false;
    }

    /**
     * @param Plugin $plugin
     * @return bool
     */
    public function unloadPlugin(Plugin $plugin) : bool{
        if($this->hasPlugin($plugin->name)){
            $ev = new PluginUnloadEvent($plugin);
            $this->connection->getEventHandler()->callEvent($ev);
            if(!$ev->isCancelled()){
                Logger::info(BashColor::RED."Unloading plugin ".BashColor::BLUE.$plugin->name);
                $plugin->unload();
                unset($this->plugins[$plugin->name]);
                $this->connection->getEventHandler()->unregisterPlugin($plugin);
                $this->connection->getScheduler()->cancelPluginTasks($plugin);
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

}