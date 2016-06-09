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

namespace IRC;

class User{

    private static $users = [];

    public static function getUser(Connection $connection, $name){
        if(isset(self::$users[$connection->getAddress()][$name])){
            return self::$users[$connection->getAddress()][$name];
        } else {
            $user = new User($connection, $name);
            self::$users[$connection->getAddress()][$name] = $user;
            return $user;
        }
    }
    
    public static function removeUser(Connection $connection, $name){
        if(isset(self::$users[$connection->getAddress()][$name])){
            unset(self::$users[$connection->getAddress()][$name]);
            return true;
        }
        return false;
    }

    private $host;
    private $connection;
    
    private $nick = "";
    private $address = "";
    private $separator = "";
    
    public function __construct(Connection $connection, $hostmask){
        $this->host = $hostmask;
        $this->connection = $connection;
        $this->nick = $this->parseNick();
        $this->address = $this->parseAddress();
        $this->separator = $this->parseSeparator();
    }

    public function getAddress(){
        return $this->address;
    }
    
    public function getNick(){
        return $this->nick;
    }
    
    /**
     * Get the address
     * @return string
     */
    private function parseAddress(){
        $address = explode("@", $this->getHostmask())[1];
        if(!empty($address)){
            return $address;
        }
        return "";
    }
    
    /**
     * Get the nickname
     * @return String
     */
    private function parseNick(){
        return str_replace(":", "", substr($this->host, 0, strpos($this->host, "!")));
    }

    public function getSeparator(){
        return $this->separator;
    }

    /**
     * @return string
     */
    private function parseSeparator(){
        $mark = strpos($this->getHostmask(), "!");
        if($mark !== false){
            if($this->getHostmask()[$mark + 1] === "~"){
                return "!~";
            }
        }
        return "!";
    }

    /**
     * Get the hostmask
     * @return String
     */
    public function getHostmask(){
        return $this->host;
    }

    /**
     * @param $message
     */
    public function sendMessage($message){
        $channel = new Channel($this->connection, $this->getNick());
        $channel->sendMessage($message);
    }

    /**
     * @param $notice
     */
    public function sendNotice($notice){
        $channel = new Channel($this->connection, $this->getNick());
        $channel->sendNotice($notice);
    }

}