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

use IRC\Event\Message\MessageSendEvent;
use IRC\Event\Notice\NoticeSendEvent;

class Channel{

    private static $channels = [];
    
    public static function getChannel(Connection $connection, $name){
        if(isset(self::$channels[$connection->getAddress()][$name])){
            return self::$channels[$connection->getAddress()][$name];
        } else {
            $channel = new Channel($connection, $name);
            self::$channels[$connection->getAddress()][$name] = $channel;
            return $channel;
        }
    }
    
    /**
     * @var String
     */
    private $name;

    private $connection;

    public function __construct(Connection $connection, $name){
        $this->name = $name;
        $this->connection = $connection;
    }

    /**
     * @return String
     */
    public function getName(){
        return $this->name;
    }

    /**
     * Send a message
     * @param $message
     */
    public function sendMessage($message){
        $ev = new MessageSendEvent($message, $this);
        $this->connection->getEventHandler()->callEvent($ev);
        if(!$ev->isCancelled()){
            $this->connection->sendData("PRIVMSG ".$this->getName()." :".$ev->getMessage());
        }
    }

    /**
     * @param $notice
     */
    public function sendNotice($notice){
        $ev = new NoticeSendEvent($notice, $this);
        $this->connection->getEventHandler()->callEvent($ev);
        if(!$ev->isCancelled()){
            $this->connection->sendData("NOTICE ".$this->getName()." :".$ev->getNotice());
        }
    }

    /**
     * Send an action
     * @param $message
     */
    public function sendAction($message){
        $this->sendMessage(chr(1)."ACTION ".$message.chr(1));
    }

    /**
     * Send CTCP
     * @param $message
     */
    public function sendCTCP($message){
        $this->sendMessage(chr(1)."CTCP ".$message.chr(1));
    }

    /**
     * Check if this channel is a query
     * @return bool
     */
    public function isQuery(){
        if($this->getName()[0] != "#"){
            return true;
        }
        return false;
    }

}