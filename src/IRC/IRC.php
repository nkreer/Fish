<?php

/*
 *
 * Fish - IRC Bot
 * Copyright (C) 2016 - 2017 Niklas Kreer
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
use IRC\Event\Connection\ConnectionUnknownCommandEvent;
use IRC\Utils\BashColor;
use IRC\Utils\JsonConfig;

class IRC {

    const VERSION = "1.1.1";
    const CODENAME = "Catfish";

    const IRC_PORT = 6667;
    const IRC_PORT_ENCRYPTED = 6697;

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
    private $configPath;

    public $verbose = false;
    public $silent = false;

    public $idleTime = false;

    private $startupTime = 0;

    public function __construct(bool $verbose = false, bool $silent = false, String $configPath = "fish.json"){
        $this->silent = $silent;
        $this->verbose = $verbose;
        self::$instance = $this;
        $this->startupTime = time();
        Logger::info(BashColor::GREEN."Starting Fish (".self::CODENAME.") v".self::VERSION);
        @mkdir("plugins");
        @mkdir("users");
        $this->loadConfig($configPath);
    }

    public function reload(){
        $this->loadConfig($this->configPath);
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

    public function loadConfig($path){
        if(!file_exists($path)){
            if($path === "fish.json"){
                Logger::info(BashColor::RED."Couldn't find configuration file. Making a new one...");
                @copy(__DIR__.DIRECTORY_SEPARATOR."Resources".DIRECTORY_SEPARATOR."fish.json", "fish.json");
            } else {
                Logger::info(BashColor::RED."No config found in ".$path.": Falling back to default...");
                $path = __DIR__.DIRECTORY_SEPARATOR."Resources".DIRECTORY_SEPARATOR."fish.json";
            }
        }
        $this->configPath = $path;
        $conf = new JsonConfig();
        $conf->loadFile($path);
        $this->config = $conf;
        $this->idleTime = $conf->getData("cpu_idle") * 1000;
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
            } else {
                // Handle unknown commands
                $unknownEvent = new ConnectionUnknownCommandEvent($connection, $run);
                $connection->getEventHandler()->callEvent($unknownEvent);
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
            if($this->idleTime){
                usleep($this->idleTime); //Chill the CPU
            }
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
                $connection->nickname = $this->getConfig()->getData("default_nickname", "FishBot");
                $connection->realname = $this->getConfig()->getData("default_realname", "Fish - IRC Bot");
                $connection->username = $this->getConfig()->getData("default_username", "Fish");
                $connection->hostname = $this->getConfig()->getData("default_hostname", "Fish");
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
                $quitMessage = $this->config->getData("default_quitmsg", "Leaving");
            }
            $connection->disconnect($quitMessage);
            unset($this->connections[$connection->getAddress()]);
            return true;
        } else {
            unset($this->connections[$connection->getAddress()]);
            return true; //These statements are kept for reasons of backwards-compatibility
        }
    }

    /**
     * @param $address
     * @return bool
     */
    public function isConnected(String $address) : bool{
        return isset($this->connections[$address]);
    }

    public function getStartupTime(){
        return $this->startupTime;
    }

}