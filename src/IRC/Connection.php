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

use IRC\Command\CommandMap;
use IRC\Event\EventHandler;
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

	public function __construct(String $address, int $port){
		$this->address = $address;
		$this->port = $port;

		$this->commandMap = new CommandMap();
		new OperatorCommands($this);
		$this->pluginManager = new PluginManager($this);
		$this->eventHandler = new EventHandler();
		$this->scheduler = new Scheduler();

		$this->trackers[] = new UserTracker($this);
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
	public function check(bool $message = true){
		if($message === true){
			$message = $this->read();
		}

		if(!empty($message)){
			$data = str_replace("\n", "", $message);
			$parsed = Parser::parse($data);
			$parsed->setConnection($this);
			if(IRC::getInstance()->devmode){
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

	public function disconnect(String $message = "Quit"){
		$this->sendData("QUIT :".$message);
		fclose($this->socket);
	}

	/**
	 * Send something to the server
	 * @param String $data
	 */
	public function sendData(String $data){
		fwrite($this->socket, $data."\n");
		if(IRC::getInstance()->devmode){
			Logger::info($this->getAddress()." > ".$data);
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
		return $this->address;
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