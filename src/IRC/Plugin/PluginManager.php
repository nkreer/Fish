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
     * @param $name
     * @return Plugin|NULL
     */
    public function getPlugin($name){
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
    public function loadPlugin($name){
        if(is_dir("plugins/".$name)){
            if(file_exists("plugins/".$name."/plugin.json")){
                $json = new JsonConfig();
                $json->loadFile("plugins/".$name."/plugin.json");
                $json = $json->getConfig();

                if($json["api"] != IRC::API_VERSION){
                    Logger::info(BashColor::YELLOW."Plugin ".$name." is not supported by this version of Fish.");
                }

                $plugin = new Plugin($name, $json["main"], $this->connection);
                $plugin->name = $name;
                $plugin->description = $json["description"];
                $plugin->apiVersion = $json["api"];
                $plugin->version = $json["version"];
                $plugin->author = $json["author"];
                if(isset($json["commands"])){
                    $plugin->commands = $json["commands"];
                }

                $key = count($this->plugins);
                $this->plugins[$plugin->name] = $plugin;
                $plugin->load();
                return $key;
            }
        }
        return false;
    }

    /**
     * @param Plugin $plugin
     */
    public function unloadPlugin(Plugin $plugin){
        if($this->hasPlugin($plugin->name)){
            Logger::info(BashColor::RED."Unloading plugin ".BashColor::BLUE.$plugin->name);
            unset($this->plugins[$plugin->name]);
            $this->connection->getEventHandler()->unregisterPlugin($plugin);
        }
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasPlugin($name){
        return isset($this->plugins[$name]);
    }

    public function reloadAll(){
        foreach($this->plugins as $plugin){
            $this->unloadPlugin($plugin);
        }

        $plugins = scandir("plugins/");
        foreach($plugins as $plugin){
            if(is_dir("plugins/".$plugin)){
                $this->loadPlugin($plugin);
            }
        }
    }

    /**
     * @param Plugin $plugin
     */
    public function reloadPlugin(Plugin $plugin){
        $name = $plugin->name;
        $this->unloadPlugin($plugin);
        $this->loadPlugin($name);
    }

}