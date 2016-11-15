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

use IRC\Authentication\NickServ;
use IRC\Command\CommandHandler;
use IRC\Command\CommandMap;
use IRC\Event\Connection\ConnectionUseEvent;
use IRC\Event\EventHandler;
use IRC\Management\ManagementCommands;
use IRC\Plugin\PluginManager;
use IRC\Scheduler\AsyncManager;
use IRC\Scheduler\Scheduler;
use IRC\Tracking\ConnectionAliveTask;
use IRC\Tracking\UserTracker;
use IRC\Utils\BashColor;

class Connection{

    /**
     * @var String
     */
    public $address;

    /**
     * @var int
     */
    private $port;

    /**
     * @var bool|String
     */
    private $password;

    private $socket;

    public $nickname = "FishBot";
    public $realname = "FISH (".IRC::CODENAME.") v".IRC::VERSION;
    public $username = "Fish";
    public $hostname = "Fish";

    /**
     * @var PluginManager
     */
    private $pluginManager;

    /**
     * @var EventHandler
     */
    private $eventHandler;

    /**
     * @var Scheduler
     */
    private $scheduler;

    /**
     * @var array
     */
    private $trackers = [];

    /**
     * @var Channel[]
     */
    private $channels = [];

    /**
     * @var CommandMap
     */
    private $commandMap;

    /**
     * @var CommandHandler
     */
    private $commandHandler;

    /**
     * @var NickServ
     */
    private $nickServ;

    /**
     * Last PING from the server
     * @var int
     */
    public $lastPing = 0;

    /**
     * @var bool
     */
    private $isConnected = false;

    public function __construct(String $address, int $port, $password = false){
        $this->address = $address;
        $this->port = $port;
        $this->password = $password;

        @mkdir("users".DIRECTORY_SEPARATOR.$this->getAddress().DIRECTORY_SEPARATOR);

        $this->eventHandler = new EventHandler();
        $this->commandMap = new CommandMap($this);
        $this->commandHandler = new CommandHandler($this);
        if(IRC::getInstance()->getConfig()->getData("disable_management", false) === false){
            new ManagementCommands($this);
        }
        $this->pluginManager = new PluginManager($this);
        $this->scheduler = new Scheduler($this);
        $this->nickServ = new NickServ($this);
        $this->load();
        $this->getPluginManager()->loadAll();
    }

    public function load(){
        $this->trackers[] = new UserTracker($this);
    }

    public function getNickServ() : NickServ{
        return $this->nickServ;
    }

    public function getCommandHandler() : CommandHandler{
        return $this->commandHandler;
    }

    public function getCommandMap() : CommandMap{
        return $this->commandMap;
    }

    public function getPluginManager() : PluginManager{
        return $this->pluginManager;
    }

    public function getEventHandler() : EventHandler{
        return $this->eventHandler;
    }

    public function getScheduler() : Scheduler{
        return $this->scheduler;
    }

    /**
     * @return string
     */
    public function read() : String{
        return fgets($this->socket);
    }

    /**
     * @param $message
     * @return bool|Command
     */
    public function check(bool $message = false){
        if($message === false){
            $message = $this->read();
        }

        if(!empty($message)){
            $data = str_replace("\n", "", $message);
            $parsed = Parser::parse($data);
            $parsed->setConnection($this);
            if(IRC::getInstance()->verbose){
                Logger::info($this->getAddress()."  ".$data);
            }
            return $parsed;
        }
        return false;
    }

    /**
     * Connect with the server
     * @return bool
     */
    public function connect() : bool{
        $this->socket = stream_socket_client($this->address.":".$this->getPort());
        if(is_resource($this->socket)){
            stream_set_blocking($this->socket, 0);
            $this->isConnected = true;
            $this->lastPing = time();
            $this->handshake();
            // Automatically reconnect after a timeout
            if($timeoutReconnect = IRC::getInstance()->getConfig()->getData("auto_reconnect_after_timeout", 500)){
                $this->getScheduler()->scheduleDelayedTask(new ConnectionAliveTask($this), $timeoutReconnect + 5);
            }
            return true;
        }
        return false;
    }

    public function handshake(){
        $this->sendData("NICK ".$this->nickname);
        $this->sendData("USER ".$this->nickname." ".$this->hostname." ".$this->username." :".$this->realname);
        if($this->password !== false){
            $this->sendData("PASS ".$this->password);
        }
    }

    public function disconnect(String $message = "Quit"){
        $this->sendData("QUIT :".$message);
        fclose($this->socket);
        $this->isConnected = false;
    }

    /**
     * Send something to the server
     * @param String $data
     */
    public function sendData(String $data){
        $ev = new ConnectionUseEvent($this, $data);
        $this->getEventHandler()->callEvent($ev);
        if(!$ev->isCancelled()){
            if($this->isConnected()){
                fwrite($this->socket, $data."\r\n");
                if(IRC::getInstance()->verbose){
                    Logger::info($this->getAddress()." > ".$data);
                }
            }
        }
    }

    public function listChannels(){
        // We need full power for this.
        IRC::getInstance()->idleTime = false;
        $this->sendData("LIST");
    }

    /**
     * Join a channel
     * @param Channel $channel
     */
    public function joinChannel(Channel $channel){
        $this->addChannel($channel);
        $this->sendData("JOIN :".$channel->getName());
        Logger::info("Joining channel ".BashColor::PURPLE.$channel->getName());
    }

    /**
     * @param Channel $channel
     */
    public function addChannel(Channel $channel){
        $this->channels[$channel->getName()] = $channel;
    }

    /**
     * @param Channel $channel
     */
    public function partChannel(Channel $channel){
        if($this->isInChannel($channel)){
            Logger::info("Leaving channel ".BashColor::PURPLE.$channel->getName());
            $this->sendData("PART :".$channel->getName());
            $this->removeChannel($channel);
        }
    }

    /**
     * @param Channel $channel
     */
    public function removeChannel(Channel $channel){
        unset($this->channels[$channel->getName()]);
    }

    /**
     * @param Channel $channel
     * @return bool
     */
    public function isInChannel(Channel $channel){
        return isset($this->channels[$channel->getName()]);
    }

    /**
     * @return Channel[]
     */
    public function getChannels() : array{
        return $this->channels;
    }

    /**
     * @param String $nick
     */
    public function changeNick(String $nick){
        $this->sendData("NICK ".$nick);
    }

    /**
     * @return String
     */
    public function getAddress() : String{
        return str_replace("ssl://", "", $this->address);
    }

    /**
     * @return int
     */
    public function getPort() : int{
        return $this->port;
    }

    public function getNick() : String{
        return $this->nickname;
    }

    public function getRealname() : String{
        return $this->realname;
    }

    public function getUsername() : String{
        return $this->getUsername();
    }

    public function getHost() : String{
        return $this->hostname;
    }

    public function getLastPing(): Int{
        return $this->lastPing;
    }

    public function isConnected() : bool{
        return $this->isConnected;
    }

}