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
    public static function getInstance() : IRC{
        return self::$instance;
    }

    /**
     * @var Connection[]
     */
    public $connections = [];

    private $config;
    public $devmode;

    public function __construct(bool $dev = false){
        $this->devmode = $dev;
        self::$instance = $this;
        Logger::info(BashColor::GREEN."Starting Fish (".self::CODENAME.") v".self::VERSION.BashColor::YELLOW." (API v".self::API_VERSION.")");
        @mkdir("plugins/");
        @mkdir("users/");

        if(!file_exists("fish.json")){
            Logger::info(BashColor::RED."Couldn't find configuration file. Making a new one...");
            $conf = new JsonConfig();
            $conf->setData("default_nickname", "FishBot");
            $conf->setData("default_realname", "Fish - IRC Bot");
            $conf->setData("default_username", "Fish");
            $conf->setData("default_hostname", "Fish");
            $conf->setData("default_quitmsg", "Leaving");
            $conf->setData("command_prefix", [".", "!", "\\", "@"]);
            $conf->setData("cpu_idle", 10);
            $conf->setData("authentication_ttl", 1200);
            $conf->setData("default_ctcp_replies", ["VERSION" => "Fish ".self::VERSION]);
            $conf->setData("spam_protection", ["enabled" => true, "max_commands" => 10, "time" => 60, "message" => "You're currently blocked from using commands because you were using too many."]);
            $conf->setData("invalid_permissions", "Sorry, you do not have the required permissions to use this command.");
            
            $conf->save("fish.json");
            $this->config = $conf;
        } else {
            $conf = new JsonConfig();
            $conf->loadFile("fish.json");
            $this->config = $conf;
        }
        stream_set_blocking(STDIN, 0);
    }

    public function getConfig() : JsonConfig{
        return $this->config;
    }

    private function cycle(){
        foreach($this->connections as $connection){
            $new = $connection->check();
            if($new != false){
                $command = $new->getCommand();
                if(is_numeric($command)){
                    $command = "_".$command;
                }
                $function = '\IRC\Protocol\\'.strtoupper($command);
                if(method_exists($function, "run")){
                    call_user_func($function."::run", $new, $connection, $this->getConfig());
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
        }
    }

    /**
     * This keeps everything alive
     */
    public function run(){
        while(true){
            if(empty($this->connections)){
                die("No more connections."); //Kill the process if no connection
            }
            $this->cycle();
            usleep($this->config->getData("cpu_idle") * 1000); //Chill
        }
    }

    /**
     * Add a connection
     * @param Connection $connection
     * @param $default
     */
    public function addConnection(Connection $connection, bool $default = true) : bool{
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
                return true;
            } else {
                Logger::info(BashColor::RED."Can't connect to ".$connection->getAddress().":".$connection->getPort());
            }
        } else {
            Logger::info(BashColor::RED."Can't connect to ".$connection->getAddress().":".$connection->getPort().": Already connected");
        }
        return false;
    }

    /**
     * Close a connection
     * @param Connection $connection
     * @param String $quitMessage
     * @return bool
     */
    public function removeConnection(Connection $connection,
                                     String $quitMessage = self::CODENAME." v".self::VERSION) : bool{
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
    public function isConnected(String $address) : bool{
        return isset($this->connections[$address]);
    }

}