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

use IRC\Event\EventHandler;
use IRC\Event\Whois\WhoisSendEvent;
use IRC\Plugin\PluginManager;
use IRC\Scheduler\Scheduler;
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
     * @var Channel[]
     */
    private $channels = [];

    public function __construct($address, $port){
        $this->address = $address;
        $this->port = $port;

        $this->pluginManager = new PluginManager($this);
        $this->eventHandler = new EventHandler();
        $this->scheduler = new Scheduler();
    }

    public function getPluginManager(){
        return $this->pluginManager;
    }

    public function getEventHandler(){
        return $this->eventHandler;
    }

    public function getScheduler(){
        return $this->scheduler;
    }

    /**
     * Check for things
     */
    public function check(){
        $next = fgets($this->socket);
        if(!empty($next)){
            $data = str_replace("\n", "", $next);
            $parsed = Parser::parse($data);
            $parsed->setConnection($this);
            Logger::info($this->getAddress()."  ".$data);
            return $parsed;
        }
        return false;
    }
    /**
     * Connect with the server
     * @return $this|bool
     */
    public function connect(){
        $this->socket = stream_socket_client($this->getAddress().":".$this->getPort());
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
    }

    public function disconnect($message = "Quit"){
        $this->sendData("QUIT :".$message);
        fclose($this->socket);
    }

    /**
     * Send something to the server
     * @param String $data
     */
    public function sendData($data){
        fwrite($this->socket, $data."\n");
        Logger::info($this->getAddress()." > ".$data);
    }

    public function whoisUser($name){
        $ev = new WhoisSendEvent($name);
        $this->getEventHandler()->callEvent($ev);
        if(!$ev->isCancelled()){
            $this->sendData("WHOIS ".$name);
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
    public function getChannels(){
        return $this->channels;
    }

    public function changeNick($nick){
        Logger::info("Changing nick from ".BashColor::PURPLE.$this->nickname.BashColor::WHITE." to ".BashColor::PURPLE.$nick);
        $this->nickname = $nick;
        $this->sendData("NICK ".$nick);
    }

    /**
     * @return String
     */
    public function getAddress(){
        return $this->address;
    }

    /**
     * @return int
     */
    public function getPort(){
        return $this->port;
    }

    public function getNick(){
        return $this->nickname;
    }

    public function getRealname(){
        return $this->realname;
    }

    public function getUsername(){
        return $this->getUsername();
    }

    public function getHost(){
        return $this->hostname;
    }

}