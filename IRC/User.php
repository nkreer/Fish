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

    /**
     * @param Connection $connection
     * @param $nick
     * @return bool|User
     */
    public static function getUserByNick(Connection $connection, $nick){
        foreach(self::$users[$connection->getAddress()] as $mask => $user){
            $parse = self::parseNick($mask);
            if($parse === $nick){
                return $user;
            }
        }
        return false;
    }

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
    private $admin = false;

    private $nick = "";
    private $address = "";
    private $separator = "";
    
    public $identified = false; 
    
    public function __construct(Connection $connection, String $hostmask){
        $this->host = $hostmask;
        $this->connection = $connection;
        $this->nick = self::parseNick($hostmask);
        $this->address = self::parseAddress($hostmask);
        $this->separator = self::parseSeparator($hostmask);
        if(is_file("users/".$this->getNick().".json")){
            $this->updateAuthenticationStatus();
            $this->admin = json_decode(file_get_contents("users/".$this->getNick().".json"), true)["admin"];
            $this->remember();
        }
    }

    public function remember(){
        $options = ["name" => $this->getNick(), "admin" => $this->admin, "lastSeen" => time()];
        return file_put_contents("users/".$this->getNick().".json", json_encode($options, JSON_PRETTY_PRINT));
    }

    public function getAddress() : String{
        return $this->address;
    }
    
    public function getNick() : String{
        return $this->nick;
    }
    
    public function isIdentified() : String{
        return $this->identified;
    }

    public function isOperator() : bool{
        if($this->isIdentified() and $this->admin){
            return true;
        }
        return false;
    }

    public function getSeparator() : String{
        return $this->separator;
    }

    public function updateAuthenticationStatus(){
        $this->connection->sendData("WHOIS ".$this->getNick());
    }

    /**
    * Get the hostmask
    * @return String
    */
    public function getHostmask() : String{
        return $this->host;
    }
    
    /**
     * Get the address
     * @return string
     */
    private static function parseAddress(String $host) : String{
        $address = explode("@", $host)[1];
        if(!empty($address)){
            return $address;
        }
        return "";
    }
    
    /**
     * Get the nickname
     * @param $host
     * @return String
     */
    private static function parseNick(String $host) : String{
        return str_replace(":", "", substr($host, 0, strpos($host, "!")));
    }

    /**
     * @return string
     */
    private static function parseSeparator(String $host) : String{
        $mark = strpos($host, "!");
        if($mark !== false){
            if($host[$mark + 1] === "~"){
                return "!~";
            }
        }
        return "!";
    }

    /**
     * @param $message
     */
    public function sendMessage(String $message){
        $channel = new Channel($this->connection, $this->getNick());
        $channel->sendMessage($message);
    }

    /**
     * @param $notice
     */
    public function sendNotice(String $notice){
        $channel = new Channel($this->connection, $this->getNick());
        $channel->sendNotice($notice);
    }

}