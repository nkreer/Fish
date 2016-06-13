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

namespace IRC\Command;

use IRC\Channel;
use IRC\Connection;
use IRC\Event\Command\CommandSendUsageTextEvent;
use IRC\IRC;
use IRC\Management\SpamProtectionResetTask;
use IRC\User;

class CommandHandler{

    private $connection;
    private $timers;
    private $config;

    public function __construct(Connection $connection){
        $this->connection = $connection;
        $this->config = IRC::getInstance()->getConfig()->getData("spam_protection");
    }

    /**
     * @param User $user
     * @return bool
     */
    public function unblockUser(User $user) : bool{
        if(isset($this->timers[$user->getAddress()])){
            unset($this->timers[$user->getAddress()]);
        }
        return true;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isBlocked(User $user) : bool{
        if($this->config["enabled"]){
            if($this->config["disable_ops"] !== true and $user->isOperator() || !$user->isOperator()){
                if(isset($this->timers[$user->getAddress()])){
                    $this->timers[$user->getAddress()] += 1;
                    if($this->timers[$user->getAddress()] >= $this->config["max_commands"]){
                        return true;
                    }
                } else {
                    $this->timers[$user->getAddress()] = 1;
                    $this->connection->getScheduler()->scheduleDelayedTask(new SpamProtectionResetTask($this, $user), $this->config["time"]);
                }
            }
        }
        return false;
    }

    /**
     * @param $cmd
     * @param User $user
     * @param Channel $channel
     * @param $args
     */
    public function handleCommand($cmd, User $user, Channel $channel, $args){
        $cmd = $this->connection->getCommandMap()->getCommand($cmd);
        if($cmd instanceof CommandInterface){
            if($this->isBlocked($user) === false){
                $result = $this->connection->getPluginManager()->command($cmd, $args[1], $user, $channel);
                if($result === false and $cmd->getUsage() !== ""){
                    $ev = new CommandSendUsageTextEvent($cmd, $user, $channel, $args[1]);
                    if(!$ev->isCancelled()){
                        $user->sendNotice("Usage: ".$cmd->getUsage());
                    }
                }
            } else {
                $user->sendNotice($this->config["message"]);
            }
        }
    }

}