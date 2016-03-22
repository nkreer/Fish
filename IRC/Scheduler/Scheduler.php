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

namespace IRC\Scheduler;

class Scheduler{

    private $tasks = [];
    private $lastCall = 0;

    public function __construct(){
        $this->lastCall = time();
    }

    public function getLastCall(){
        return $this->lastCall;
    }

    /**
     * Schedule a task
     * @param TaskInterface $task
     * @param $when
     * @return String
     */
    public function scheduleDelayedTask(TaskInterface $task, $when){
        $when = time() + $when;
        $this->tasks[$when][] = $task;
        return $when." ".(count($this->tasks[$when]) - 1);
    }

    /**
     * @param TaskInterface $task
     * @param $when
     * @return string
     */
    public function scheduleTaskForTime(TaskInterface $task, $when){
        $this->tasks[$when][] = $task;
        return $when." ".(count($this->tasks[$when]) - 1);
    }

    /**
     * Register the same task multiple times
     * @param TaskInterface $task
     * @param $interval
     * @param $times
     * @return array
     */
    public function scheduleMultipleDelayedTask(TaskInterface $task, $interval, $times){
        $ids = [];
        $time = $interval;
        for($run = 1; $run <= $times; $run++){
            $ids[$time] = $this->scheduleDelayedTask($task, $time);
            $time += $interval;
        }
        return $ids;
    }

    /**
     * @param $id
     * @return bool
     */
    public function cancelTask($id){
        $id = explode(" ", $id);
        if(isset($this->tasks[$id[0]][$id[1]])){
            unset($this->tasks[$id[0]][$id[1]]);
            return true;
        }
        return false;
    }

    public function call($time = -1){
        if($time === -1){
            $time = time();
        }

        if(isset($this->tasks[$time])){
            foreach($this->tasks[$time] as $task){
                if($task instanceof TaskInterface){
                    $task->onRun();
                }
            }
            unset($this->tasks[$time]);
        }

        if($time == time()){
            $this->lastCall = $time; //Overwrite the value if this is up to date
        }
    }

}