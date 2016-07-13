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

use IRC\Event\Connection\ConnectionActivityEvent;
use IRC\Utils\BashColor;
use IRC\Utils\JsonConfig;

class IRC{

    const VERSION = 2.0;
    const API_VERSION = 2.0;
    const CODENAME = "Catfish";

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

    /**
     * @var JsonConfig
     */
    private $config;

    public $verbose = false;
    public $silent = false;

    public function __construct(bool $verbose = false, bool $silent = false){
        $this->silent = $silent;
        $this->verbose = $verbose;
        self::$instance = $this;
        Logger::info(BashColor::GREEN."Starting Fish (".self::CODENAME.") v".self::VERSION.BashColor::YELLOW." (API v".self::API_VERSION.")");
        @mkdir("plugins");
        @mkdir("users");
        $this->loadConfig();
    }

    public function reload(){
        $this->loadConfig();
        foreach($this->connections as $connection){
            $connection->getEventHandler()->unregisterAll();
            $connection->getScheduler()->cancelAll();
            $connection->load();
            $connection->getPluginManager()->reloadAll();
        }
    }

    public function stop(){
        Logger::info("Stopping...");
        foreach($this->connections as $connection){
            $this->removeConnection($connection);
            $connection->getPluginManager()->unloadAll();
        }
        exit(0);
    }

    public function loadConfig(){
        if(!file_exists("fish.json")){
            Logger::info(BashColor::RED."Couldn't find configuration file. Making a new one...");
            @copy(__DIR__.DIRECTORY_SEPARATOR."Resources".DIRECTORY_SEPARATOR."fish.json", "fish.json");
        }
        $conf = new JsonConfig();
        $conf->loadFile("fish.json");
        $this->config = $conf;
    }

    public function getCommandPrefix() : String{
        return $this->getConfig()->getData("command_prefix")[0];
    }

    public function getConfig() : JsonConfig{
        return $this->config;
    }

    public function cycle(){
        foreach($this->connections as $connection){
            $new = $connection->check();
            if($new instanceof Command){
                $this->handle($new, $connection);
            }
            $this->runScheduler($connection);
        }
    }

    public function runScheduler(Connection $connection){
        //Do catchup calls if we've missed any
        if(time() - $connection->getScheduler()->getLastCall() >= 2){
            for($time = time(); $time > $connection->getScheduler()->getLastCall(); $time--){
                $connection->getScheduler()->call($time);
            }
        }

        //Regularly run scheduler
        $connection->getScheduler()->call();
    }

    public function handle(Command $run, Connection $connection){
        $ev = new ConnectionActivityEvent($connection, $run);
        $connection->getEventHandler()->callEvent($ev);
        if(!$ev->isCancelled()){
            $command = $run->getCommand();
            if(is_numeric($command)){
                $command = "_".$command;
            }
            $function = '\IRC\Protocol\\'.strtoupper($command);
            if(method_exists($function, "run")){
                // Most stupid hack ever, but it gets the job done
                call_user_func($function."::run", $run, $connection, $this->getConfig());
            }
        }
    }

    /**
     * This keeps everything alive
     */
    public function run(){
        while(true){
            if(empty($this->connections)){
                $this->stop(); // Stop
            }
            $this->cycle();
            usleep($this->config->getData("cpu_idle") * 1000); //Chill
        }
    }

    /**
     * @param Connection $connection
     * @param bool $default
     * @return bool
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
            if($result){
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
    public function removeConnection(Connection $connection, String $quitMessage = null) : bool{
        if($this->isConnected($connection->getAddress())){
            Logger::info(BashColor::RED."Disconnecting ".$connection->getAddress().":".$connection->getPort());
            if($quitMessage === null){
                $quitMessage = $this->config->getData("default_quitmsg");
            }
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