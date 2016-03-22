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

use IRC\Event\Channel\ChannelLeaveEvent;
use IRC\Event\Channel\JoinChannelEvent;
use IRC\Event\Channel\UserQuitEvent;
use IRC\Event\Command\CommandEvent;
use IRC\Event\Command\CommandLineEvent;
use IRC\Event\Message\MessageReceiveEvent;
use IRC\Event\Ping\PingEvent;
use IRC\Utils\BashColor;
use IRC\Utils\JsonConfig;

class IRC{

    const VERSION = 1.0;
    const API_VERSION = 1.0;
    const CODENAME = "Tuna";

    public static $instance;

    /**
     * @return IRC
     */
    public static function getInstance(){
        return self::$instance;
    }

    /**
     * @var Connection[]
     */
    public $connections = [];

    private $config;

    public function __construct(){
        self::$instance = $this;
        Logger::info(BashColor::GREEN."Starting Fish (".self::CODENAME.") v".self::VERSION.BashColor::YELLOW." (API v".self::API_VERSION.")");
        @mkdir("plugins/");

        if(!file_exists("fish.json")){
            Logger::info(BashColor::RED."Couldn't find configuration file. Making a new one...");
            $conf = new JsonConfig();
            $conf->setData("default_nickname", "FishBot");
            $conf->setData("default_realname", "Fish - IRC Bot");
            $conf->setData("default_username", "Fish");
            $conf->setData("default_hostname", "Fish");
            $conf->setData("default_quitmsg", "Leaving");
            $conf->setData("command_prefix", [".", "!", "\\", "@"]);

            $conf->save("fish.json");
            $this->config = $conf;
        } else {
            $conf = new JsonConfig();
            $conf->loadFile("fish.json");
            $this->config = $conf;
        }
        stream_set_blocking(STDIN, 0);
    }

    public function getConfig(){
        return $this->config;
    }

    /**
     * This keeps all the connections alive
     */
    public function run(){
        while(true){
            $data = fgets(STDIN); //Read from STDIN and check for commands
            if(!empty($data)){
                $event = new CommandLineEvent(str_replace("\n", "", $data));
            }

            if(empty($this->connections)){
                die(); //Kill the process if no connection
            }

            foreach($this->connections as $connection){
                $new = $connection->check();
                if($new != false){
                    switch($new->getCommand()){
                        case 'PING':
                            $ev = new PingEvent();
                            $connection->getEventHandler()->callEvent($ev);
                            if(!$ev->isCancelled()){
                                $connection->sendData("PONG ".$new->getArgs()[0]); //Reply to Pings
                            }
                            unset($ev); //Remove it from the memory
                            break;
                        case 'PRIVMSG':
                            $user = new User($connection, $new->getPrefix());
                            $arg = $new->getArgs();
                            if($new->getArg(0) === $connection->nickname){
                                $channel = new Channel($connection, $user->getNick());
                            } else {
                                $channel = new Channel($connection, $arg[0]);
                            }
                            unset($arg[0]);
                            $args = explode(":", implode(" ", $arg), 2);
                            if(!in_array($args[1][0], $this->config->getData("command_prefix"))){ //Decide wether the message is a message or a command
                                $ev = new MessageReceiveEvent($args[1], $user, $channel);
                                $connection->getEventHandler()->callEvent($ev);
                                if(!$ev->isCancelled()){
                                    Logger::info(BashColor::HIGHLIGHT.$ev->getChannel()->getName()." ".$ev->getUser()->getNick().":".BashColor::REMOVE." ".$ev->getMessage()); //Display the message to the console
                                }
                            } else {
                                $args[1] = substr($args[1], 1);
                                $args[1] = explode(" ", $args[1]);
                                $cmd = $args[1][0];
                                unset($args[1][0]);
                                Logger::info(BashColor::CYAN.$user->getNick()." > ".$cmd." ".implode(" ", $args[1]));
                                $ev = new CommandEvent($cmd, $args[1], $channel, $user);
                                $connection->getEventHandler()->callEvent($ev);
                            }
                            unset($ev); //Remove it from the memory
                            break;
                        case 'JOIN':
                            //Tell the plugins that a user joined
                            $channel = new Channel($connection, str_replace(":", "", $new->getArg(0)));
                            $user = new User($connection, $new->getPrefix());
                            $ev = new JoinChannelEvent($channel, $user);
                            $connection->getEventHandler()->callEvent($ev);
                            if(!$ev->isCancelled()){
                                Logger::info($user->getNick()." joined ".$channel->getName());
                            }
                            break;
                        case 'PART':
                            //Tell the plugins that a user parted
                            $channel = new Channel($connection, str_replace(":", "", $new->getArg(0)));
                            $user = new User($connection, $new->getPrefix());
                            $ev = new ChannelLeaveEvent($channel, $user);
                            $connection->getEventHandler()->callEvent($ev);
                            if(!$ev->isCancelled()){
                                Logger::info($user->getNick()." left ".$channel->getName());
                            }
                            break;
                        case 'QUIT':
                            //Tell the plugins that a user quit
                            $user = new User($connection, $new->getPrefix());
                            $ev = new UserQuitEvent($user);
                            $connection->getEventHandler()->callEvent($ev);
                            if(!$ev->isCancelled()){
                                Logger::info($user->getNick()." quit");
                            }
                            break;
                    }
                }

                //Do catchup calls if we've missed any
                if(time() - $connection->getScheduler()->getLastCall() >= 2){
                    for($time = time(); $time > $connection->getScheduler()->getLastCall(); $time--){
                        $connection->getScheduler()->call($time);
                    }
                }

                //Regularly run scheduler
                $connection->getScheduler()->call();
                if(isset($event)){
                    $connection->getEventHandler()->callEvent($event); //The special CommandLineEvent gets called if it has been created earlier
                }

            }
            if(isset($event)){
                unset($event); //Destroy the event
            }
        }
    }

    /**
     * Add a connection
     * @param Connection $connection
     */
    public function addConnection(Connection $connection, $default = true){
        if(!$this->isConnected($connection->getAddress())){
            Logger::info(BashColor::CYAN."Connecting to ".$connection->getAddress().":".$connection->getPort()."...");
            //Setting up connection details
            if($default === true){
                $connection->nickname = $this->getConfig()->getData("default_nickname");
                $connection->realname = $this->getConfig()->getData("default_realname");
                $connection->username = $this->getConfig()->getData("default_username");
                $connection->hostname = $this->getConfig()->getData("default_hostname");
            }
            $result = $connection->connect();
            if($result instanceof Connection){
                $this->connections[$connection->getAddress()] = $connection;
                Logger::info(BashColor::GREEN."Connected to ".$connection->getAddress().":".$connection->getPort());
            } else {
                Logger::info(BashColor::RED."Can't connect to ".$connection->getAddress().":".$connection->getPort());
            }
        } else {
            Logger::info(BashColor::RED."Can't connect to ".$connection->getAddress().":".$connection->getPort().": Already connected");
        }
    }

    /**
     * Close a connection
     * @param Connection $connection
     * @param String $quitMessage
     * @return bool
     */
    public function removeConnection(Connection $connection, $quitMessage = self::CODENAME." v".self::VERSION){
        if($this->isConnected($connection->getAddress())){
            Logger::info(BashColor::RED."Disconnecting ".$connection->getAddress().":".$connection->getPort());
            $connection->disconnect($quitMessage);
            unset($this->connections[$connection->getAddress()]);
            return true;
        }
        return false;
    }

    /**
     * @param $address
     * @return bool
     */
    public function isConnected($address){
        return isset($this->connections[$address]);
    }

}