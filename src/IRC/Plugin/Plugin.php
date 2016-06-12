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

	public function __construct(String $name, array $json, Connection $connection){
		if(file_exists("plugins".DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR."plugin.json")){
			Logger::info(BashColor::GREEN."Loading plugin ".BashColor::BLUE.$name);

			$this->name = $json["name"];
			$this->description = $json["description"];
			$this->apiVersion = $json["api"];
			$this->version = $json["version"];
			$this->author = $json["author"];
			$this->main = $json["main"];
			if(isset($json["commands"])){
				$this->commands = $json["commands"];
			}

			$info = new \SplFileInfo("plugins".DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR.$this->main);
			$class = new \ReflectionClass("\\".$name."\\".$info->getBasename(".php")); //Taking care of using the correct namespace
			$this->class = $class->newInstanceWithoutConstructor();
			$this->reflectionClass = $class;
			$this->class->connection = $connection;
			$this->class->plugin = $this;
		}
	}

	public function load(){
		if($this->reflectionClass->hasMethod("onLoad")){
			$this->class->onLoad(); //Call the onLoad method
		}
	}

	public function unload(){
		if($this->reflectionClass->hasMethod("onDisable")){
			$this->class->onDisable(); //Call the onDisable method
		}
	}

}