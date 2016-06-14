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

use IRC\Authentication\AuthenticationStatus;
use IRC\Authentication\UpdateAuthenticationStatusTask;
use IRC\Command\CommandSender;

class User implements CommandSender{

    private static $users = [];
    public $identified = AuthenticationStatus::UNCHECKED;
    private $host;
    private $connection;
    private $admin = false;
    private $nick = "";
    private $address = "";
    private $separator = "";
    private $permissions = [];

    public function __construct(Connection $connection, String $hostmask){
        $this->host = $hostmask;
        $this->connection = $connection;
        $this->nick = self::parseNick($hostmask);
        $this->address = self::parseAddress($hostmask);
        $this->separator = self::parseSeparator($hostmask);
        if(is_file("users".DIRECTORY_SEPARATOR.$connection->getAddress().DIRECTORY_SEPARATOR.$this->getNick().".json")){
            $this->updateAuthenticationStatus();
            $data = json_decode(file_get_contents("users".DIRECTORY_SEPARATOR.$connection->getAddress().DIRECTORY_SEPARATOR.$this->getNick().".json"), true);
            $this->admin = $data["admin"];
            $this->permissions = $data["permissions"];
            $this->remember();
        }
    }

    /**
     * @param $permission
     * @return bool
     */
    public function hasPermission($permission) : bool{
        if($permission !== true or !$this->isOperator()){
            return isset($this->permissions[$permission]);
        }
        return true;
    }

    /**
     * @param String $permission
     */
    public function addPermission(String $permission){
        $this->permissions[$permission] = true;
    }

    /**
     * @param String $permission
     */
    public function removePermission(String $permission){
        if($this->hasPermission($permission)){
            unset($this->permissions[$permission]);
        }
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
     * Get the address
     * @return string
     */
    private static function parseAddress(String $host) : String{
        $address = explode("@", $host);
        if(!empty($address) and isset($address[1])){
            return $address[1];
        }
        return "";
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
     * @return String
     */
    public function getNick() : String{
        return $this->nick;
    }

    public function updateAuthenticationStatus(){
        $this->identified = AuthenticationStatus::UNIDENTIFIED;
        $this->connection->sendData("WHOIS ".$this->getNick());
        $this->connection->getScheduler()->scheduleDelayedTask(new UpdateAuthenticationStatusTask($this), IRC::getInstance()->getConfig()->getData("authentication_ttl"));
    }

    /**
     * @return int
     */
    public function remember() : int{
        $options = ["name" => $this->getNick(), "admin" => $this->admin, "lastSeen" => time()];
        return file_put_contents("users".DIRECTORY_SEPARATOR.$this->connection->getAddress().DIRECTORY_SEPARATOR.$this->getNick().".json", json_encode($options, JSON_PRETTY_PRINT));
    }

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

    /**
     * @param Connection $connection
     * @param $name
     * @return User
     */
    public static function getUser(Connection $connection, $name){
        if(isset(self::$users[$connection->getAddress()][$name])){
            return self::$users[$connection->getAddress()][$name];
        } else {
            $user = new User($connection, $name);
            self::$users[$connection->getAddress()][$name] = $user;
            return $user;
        }
    }

    /**
     * @param Connection $connection
     * @param $name
     * @return bool
     */
    public static function removeUser(Connection $connection, $name){
        if(isset(self::$users[$connection->getAddress()][$name])){
            unset(self::$users[$connection->getAddress()][$name]);
            return true;
        }
        return false;
    }

    /**
     * @param Connection $connection
     * @param $name
     * @return User|bool
     */
    public static function reloadUser(Connection $connection, $name){
        self::removeUser($connection, $name);
        return self::getUser($connection, $name);
    }

    /**
     * Alias for getNick
     * @return String
     */
    public function getName() : String{
        return $this->nick;
    }

    /**
     * @return String
     */
    public function getAddress() : String{
        return $this->address;
    }

    /**
     * @return bool
     */
    public function isOperator() : bool{
        if($this->getAuthenticationStatus() === AuthenticationStatus::IDENTIFIED and $this->admin){
            return true;
        }
        return false;
    }

    /**
     * @return String
     */
    public function getAuthenticationStatus() : String{
        return $this->identified;
    }

    /**
     * @return String
     */
    public function getSeparator() : String{
        return $this->separator;
    }

    /**
     * Get the hostmask
     * @return String
     */
    public function getHostmask() : String{
        return $this->host;
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