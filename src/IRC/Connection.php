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

    public $nickname = "IRCBoot";
    public $realname = IRC::CODENAME." v".IRC::VERSION;
    public $username = "IRCBot";
    public $hostname = "IRCBot";

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

        $this->pluginManager = new PluginManager();
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
            $data = explode(" ", $data);
            unset($data[0]);
            $data = implode(" ", $data);
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

    public function whoisUser(User $user){
        $ev = new WhoisSendEvent($user);
        //TODO - Call event
        if(!$ev->isCancelled()){
            $this->sendData("WHOIS ".$user->getNick());
        }
    }

    /**
     * Join a channel
     * @param Channel $channel
     */
    public function joinChannel(Channel $channel){
        $this->channels[] = $channel;
        $this->sendData("JOIN :".$channel->getName());
    }

    /**
     * @return Channel[]
     */
    public function getChannels(){
        return $this->channels;
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

}