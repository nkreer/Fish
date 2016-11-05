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

namespace IRC\Tracking;

use IRC\Connection;
use IRC\IRC;
use IRC\Logger;
use IRC\Scheduler\Task;
use IRC\Utils\BashColor;

class ConnectionAliveTask extends Task{

    private $connection;

    public function __construct(Connection $connection){
        $this->connection = $connection;
    }

    public function onRun(){
        $time = IRC::getInstance()->getConfig()->getData("auto_reconnect_after_timeout");
        if($time){
            if($this->connection->getLastPing() <= ($time + time()) and $this->connection->isConnected()){
                // Possibly time-outed. Attempt a reconnect
                $this->connection->disconnect();
                Logger::info(BashColor::CYAN."Reconnecting to ".$this->connection->getAddress());
                if($this->connection->connect()){
                    Logger::info(BashColor::GREEN."Successfully reconnected.");
                    // Re-schedule this task
                    $this->reschedule($time);
                } else {
                    Logger::info(BashColor::RED."Error while reconnecting. Terminating connection.");
                    IRC::getInstance()->removeConnection($this->connection);
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