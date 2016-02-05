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

use IRC\Utils\JsonConfig;

class PluginManager{

    private $plugins = [];

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

                $plugin = new Plugin($name, $json["main"]);
                $plugin->name = $name;
                $plugin->description = $json["description"];
                $plugin->apiVersion = $json["api"];
                $plugin->version = $json["version"];
                $plugin->author = $json["author"];
                $key = count($this->plugins);
                $this->plugins[$plugin->name] = $plugin;
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
            unset($this->plugins[$plugin->name]);
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