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

namespace IRC\Utils;

class JsonConfig{

    private $config = [];

    public function loadFile(String $filename){
        $this->config = json_decode(file_get_contents($filename), true);
    }

    public function setData(String $key, $value){
        $this->config[$key] = $value;
    }

    public function getData(String $key){
        return $this->config[$key];
    }

    public function hasData(String $key) : bool{
        return isset($this->config[$key]);
    }

    public function getConfig(){
        return $this->config;
    }

    public function save(String $filename){
        return file_put_contents($filename, json_encode($this->config, JSON_PRETTY_PRINT));
    }

}