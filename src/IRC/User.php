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
use IRC\Scheduler\Task;

class User implements CommandSender{

    private static $users = [];
    public $identified = AuthenticationStatus::UNCHECKED;
    private $host;
    private $connection;
    public $admin = false;
    private $nick = "";
    private $address = "";
    private $permissions = [];

    public function __construct(Connection $connection, String $hostmask){
        $this->host = $hostmask;
        $this->connection = $connection;
        $this->nick = self::parseNick($hostmask);
        $this->address = self::parseAddress($hostmask);
        if(is_file("users".DIRECTORY_SEPARATOR.$connection->getAddress().DIRECTORY_SEPARATOR.$this->getNick().".json")){
            $this->updateAuthenticationStatus();
            $data = json_decode(file_get_contents("users".DIRECTORY_SEPARATOR.$connection->getAddress().DIRECTORY_SEPARATOR.$this->getNick().".json"), true);
            $this->admin = $data["admin"];
            $this->permissions = $data["permissions"];
            $this->remember();
        }
    }

    /**
     * @return array
     */
    public function getPermissions() : array{
        $permissions = [];
        foreach($this->permissions as $permission => $yes){
            $permissions[] = $permission;
        }
        return $permissions;
    }

    /**
     * @param $permission
     * @return bool
     */
    public function hasPermission($permission) : bool{
        if($permission === false || $this->isOperator()  || isset($this->permissions[$permission])){
            return true;
        }
        return false;
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
     * @return String
     */
    public function getNick() : String{
        return $this->nick;
    }

    public function updateAuthenticationStatus(){
        $this->identified = AuthenticationStatus::UNIDENTIFIED;
        $this->connection->sendData("WHOIS ".$this->getNick());
    }

    /**
     * @return int
     */
    public function remember() : int{
        $options = ["name" => $this->getNick(), "admin" => $this->admin, "lastSeen" => time(), "permissions" => $this->getPermissions()];
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
    public static function exists(Connection $connection, $name){
        return isset(self::$users[$connection->getAddress()], $name);
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
        if($this->getAuthenticationStatus() === AuthenticationStatus::IDENTIFIED and $this->admin === true){
            return true;
        }
        return false;
    }

    /**
     * @return int
     */
    public function getAuthenticationStatus() : int{
        return $this->identified;
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

    /**
     * @param Channel $channel
     * @param String $mode
     */
    public function setMode(Channel $channel, String $mode){
        $this->connection->sendData("MODE ".$channel->getName()." +".$mode." ".$this->getNick());
    }

    /**
     * @param Channel $channel
     * @param String $mode
     */
    public function takeMode(Channel $channel, String $mode){
        $this->connection->sendData("MODE ".$channel->getName()." -".$mode." ".$this->getNick());
    }

}