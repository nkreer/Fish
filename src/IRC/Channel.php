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

use IRC\Command\Command as UserCommand;
use IRC\Command\CommandSender;
use IRC\Event\Invite\InviteUserEvent;
use IRC\Event\Message\MessageSendEvent;
use IRC\Event\Notice\NoticeSendEvent;

class Channel implements CommandSender{

    private static $channels = [];

    public static function getChannel(Connection $connection, String $name){
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
    private $users = [];

    public $topic = "";
    public $topicTime = 0;

    public function __construct(Connection $connection, String $name){
        $this->name = $name;
        $this->connection = $connection;
    }

    public function __toString(){
        return $this->name;
    }

    /**
     * @return String
     */
    public function getName() : String{
        return $this->name;
    }

    /**
     * Send a message
     * @param $message
     */
    public function sendMessage(String $message){
        $ev = new MessageSendEvent($message, $this);
        $this->connection->getEventHandler()->callEvent($ev);
        if(!$ev->isCancelled()){
            $this->connection->sendData("PRIVMSG ".$this->getName()." :".$ev->getMessage());
        }
    }

    /**
     * @param $notice
     */
    public function sendNotice(String $notice){
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
    public function sendAction(String $message){
        $this->sendMessage(chr(1)."ACTION ".$message.chr(1));
    }

    /**
     * Check if this channel is a query
     * @return bool
     */
    public function isQuery() : bool{
        if($this->getName()[0] != "#"){
            return true;
        }
        return false;
    }

    public function clearUserList(){
        $this->users = [];
    }

    /**
     * @return array
     */
    public function getUsers() : array{
        return $this->users;
    }

    /**
     * @param User $user
     */
    public function addUser(User $user){
        $this->users[$user->getNick()] = $user;
    }

    /**
     * @param $nick
     * @return bool
     */
    public function removeUser(String $nick) : bool{
        if(isset($this->users[$nick])){
            unset($this->users[$nick]);
            return true;
        }
        return false;
    }

    /**
     * @param $nick
     * @return bool
     */
    public function hasUser($nick) : bool{
        return isset($this->users[$nick]);
    }

    /**
     * @return string
     */
    public function getTopic() : String{
        return $this->topic;
    }

    /**
     * @param $text
     */
    public function setTopic(String $text){
        $this->topic = $text;
        $this->connection->sendData("TOPIC ".$this->getName()." :".$text);
        $this->topicTime = time();
    }

    /**
     * @return int
     */
    public function getLastTopicTime(){
        return $this->topicTime;
    }

    /**
     * @param User $user
     */
    public function ban(User $user, $kick = false){
        $this->setMode("b *!*@".$user->getAddress());
        if($kick == true){
            $this->kick($user);
        }
    }

    /**
     * @param String $mask
     */
    public function unban(String $mask){
        $this->takeMode("b ".$mask);
    }

    /**
     * @param User $user
     * @param String $reason
     */
    public function kick(User $user, String $reason = "Kicked."){
        $this->connection->sendData("KICK ".$this->getName()." ".$user->getNick()." :".$reason);
        User::removeUser($this->connection, $user->getHostmask()); //Remove from connection
        $this->removeUser($user->getNick()); // Remove from channel
    }

    /**
     * @param $mode
     */
    public function setMode($mode){
        $this->connection->sendData("MODE ".$this->getName()." +".$mode);
    }

    /**
     * @param String $mode
     */
    public function takeMode(String $mode){
        $this->connection->sendData("MODE ".$this->getName()." -".$mode);
    }

    /**
     * Make a user execute a command
     * @param User $user
     * @param UserCommand $command
     * @param array $args
     */
    public function executeCommand(User $user, UserCommand $command, array $args = []){
        $this->connection->getCommandHandler()->handleCommand($command, $user, $this, $args);
    }

    /**
     * @param String $nick
     */
    public function inviteUser(String $nick){
        $ev = new InviteUserEvent($this, $nick);
        $this->connection->getEventHandler()->callEvent($ev);
        if(!$ev->isCancelled()){
            $this->connection->sendData("INVITE ".$nick." ".$this->getName());
        }
    }

}