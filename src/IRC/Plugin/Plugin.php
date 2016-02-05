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

use IRC\Logger;
use IRC\Utils\BashColor;

class Plugin{

    public $name;
    public $description;
    public $apiVersion;
    public $version;
    public $author;

    public $reflectionClass;

    /**
     * @var PluginBase
     */
    public $class;

    public $isEnabled = false;

    public function __construct($name, $main){
        if(file_exists("plugins/".$name."/plugin.json")){
            Logger::info(BashColor::GREEN."Loading plugin ".BashColor::BLUE.$name);

            $info = new \SplFileInfo("plugins/".$name."/".$main);
            include("plugins/".$name."/".$main);
            $class = new \ReflectionClass($name."\\".$info->getBasename(".php")); //Taking care of using the correct namespace
            $this->class = $class->newInstanceWithoutConstructor();
            $this->reflectionClass = $class;
            if($this->reflectionClass->hasMethod("onLoad")){
                $this->class->onLoad(); //Call the onLoad method
            }
        }
    }

    public function enable(){
        $this->isEnabled = true;
    }

    public function disable(){
        $this->isEnabled = false;
    }

}