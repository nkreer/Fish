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

namespace IRC\Tracking;

use IRC\Connection;
use IRC\IRC;
use IRC\Logger;
use IRC\Scheduler\Task;
use IRC\Utils\BashColor;

class ConnectionAliveTask extends Task{

    private $connection;
    private $attempts = 0;

    public function __construct(Connection $connection){
        $this->connection = $connection;
    }

    public function onRun(){
        $time = (int)IRC::getInstance()->getConfig()->getData("auto_reconnect_after_timeout", 500);
        $max_attempts = (int)IRC::getInstance()->getConfig()->getData("max_reconnect_attempts", 5);
        if($time){
            if((time() - $this->connection->getLastPing()) >= $time and $this->connection->isConnected()){
                // Possibly time-outed. Attempt a reconnect
                Logger::info(BashColor::RED."Lost connection to ".$this->connection->getAddress()." - Reconnecting...");
                $this->connection->disconnect("Time-outed.");
                if($this->connection->connect()){
                    Logger::info(BashColor::GREEN."Successfully reconnected.");
                    // Re-schedule this task
                    $this->reschedule($time);
                    $this->attempts = 0; // Successfully reconnected, reset
                } else {
                    Logger::info(BashColor::RED."Error while reconnecting.");
                    $this->attempts++;
                    if($this->attempts > $max_attempts){
                        // Remove the connection
                        Logger::info(BashColor::RED."Terminating connection.");
                        IRC::getInstance()->removeConnection($this->connection);
                    } else {
                        Logger::info(BashColor::CYAN."Next attempt in ".$time."s");
                        $this->reschedule($time);
                    }
                }
            } else {
                $this->reschedule($time);
            }
        }
    }

    public function reschedule($time){
        $this->connection->getScheduler()->scheduleTaskForTime($this, time() + $time + 5);
    }

}