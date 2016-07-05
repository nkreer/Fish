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
use IRC\Management\OperatorCommands;
use IRC\Plugin\PluginManager;
use IRC\Scheduler\Scheduler;
use IRC\Tracking\UserTracker;
use IRC\Utils\BashColor;

class Connection{

    /**
     * @var String
     */
    private $address;
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

    public function __construct(String $address, int $port, $password = false){
        $this->address = $address;
        $this->port = $port;
        $this->password = $password;

        @mkdir("users".DIRECTORY_SEPARATOR.$this->getAddress().DIRECTORY_SEPARATOR);

        $this->eventHandler = new EventHandler();
        $this->commandMap = new CommandMap($this);
        $this->commandHandler = new CommandHandler($this);
        if(IRC::getInstance()->getConfig()->getData("disable_management") === false){
            new ManagementCommands($this);
        }
        $this->pluginManager = new PluginManager($this);
        $this->scheduler = new Scheduler();
        $this->nickServ = new NickServ($this);
        $this->load();
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
     * @return $this|bool
     */
    public function connect(){
        $this->socket = stream_socket_client($this->address.":".$this->getPort());
        if(is_resource($this->socket)){
            stream_set_blocking($this->socket, 0);
            $this->handshake();
            $this->getPluginManager()->loadAll();
            return $this;
        }
        return false;
    }

    public function handshake(){
        $this->sendData("USER ".$this->nickname." ".$this->hostname." ".$this->username." :".$this->realname);
        $this->sendData("NICK ".$this->nickname);
        if($this->password !== false){
            $this->sendData("PASS ".$this->password);
        }
    }

    public function disconnect(String $message = "Quit"){
        $this->sendData("QUIT :".$message);
        fclose($this->socket);
    }

    /**
     * Send something to the server
     * @param String $data
     */
    public function sendData(String $data){
        $ev = new ConnectionUseEvent($this, $data);
        $this->getEventHandler()->callEvent($ev);
        if(!$ev->isCancelled()){
            fwrite($this->socket, $data."\n");
            if(IRC::getInstance()->verbose){
                Logger::info($this->getAddress()." > ".$data);
            }
        }
    }

    /**
     * Join a channel
     * @param Channel $channel
     */
    public function joinChannel(Channel $channel){
        $this->channels[$channel->getName()] = $channel;
        $this->sendData("JOIN :".$channel->getName());
        Logger::info("Joining channel ".BashColor::PURPLE.$channel->getName());
    }

    /**
     * @param Channel $channel
     */
    public function partChannel(Channel $channel){
        if($this->isInChannel($channel)){
            Logger::info("Leaving channel ".BashColor::PURPLE.$channel->getName());
            $this->sendData("PART :".$channel->getName());
            unset($this->channels[$channel->getName()]);
        }
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

    public function changeNick(String $nick){
        Logger::info("Changing nick from ".BashColor::PURPLE.$this->nickname.BashColor::WHITE." to ".BashColor::PURPLE.$nick);
        $this->nickname = $nick;
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

}