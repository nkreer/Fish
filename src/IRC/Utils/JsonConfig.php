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
    private $filename = false;

    public function loadFile(String $filename){
        $this->config = json_decode(file_get_contents($filename), true);
        $this->filename = $filename;
    }

    public function setData(String $key, $value){
        $this->config[$key] = $value;
    }

    public function getData(String $key, $default = null){
        if($this->hasData($key)){
            return $this->config[$key];
        } else {
            $this->setData($key, $default);
            if($this->filename){
                // Save it, so we can use it later.
                $this->save($this->filename);
            }
            return $default;
        }
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