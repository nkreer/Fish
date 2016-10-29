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

use IRC\Connection;

class Scheduler{

    private $tasks = [];
    private $plugins = [];
    private $connection;

    private $lastCall = 0;

    public function __construct(Connection $connection){
        $this->lastCall = time();
        $this->connection = $connection;
    }

    /**
     * @return int
     */
    public function getLastCall(){
        return $this->lastCall;
    }

    public function cancelAll(){
        foreach($this->plugins as $name => $tasks){
            $this->cancelPluginTasks($name);
        }
        $this->tasks = [];
    }

    /**
     * @param $plugin
     */
    public function cancelPluginTasks($plugin){
        if(!empty($this->plugins[$plugin])){
            foreach($this->plugins[$plugin] as $id){
                $this->cancelTask($id);
            }
        }
    }

    /**
     * @param $id
     * @return bool
     */
    public function cancelTask(String $id) : bool{
        $id = explode(" ", $id);
        if(isset($this->tasks[$id[0]][$id[1]])){
            unset($this->tasks[$id[0]][$id[1]]);
            return true;
        }
        return false;
    }

    /**
     * @param TaskInterface $task
     * @param $when
     * @return string
     */
    public function scheduleTaskForTime(TaskInterface $task, int $when) : String{
        $this->tasks[$when][] = $task;
        $id = $when." ".(count($this->tasks[$when]) - 1);
        if($task instanceof PluginTask){
            $this->plugins[$task->getOwner()->getPlugin()->name][] = $id;
        }
        return $id;
    }

    /**
     * Register the same task multiple times
     * @param TaskInterface $task
     * @param $interval
     * @param $times
     * @return array
     */
    public function scheduleMultipleDelayedTask(TaskInterface $task, int $interval, int $times) : array{
        $ids = [];
        $time = $interval;
        for($run = 1; $run <= $times; $run++){
            $ids[$time] = $this->scheduleDelayedTask($task, $time);
            $time += $interval;
        }

        if($task instanceof PluginTask){
            foreach($ids as $id){
                $this->plugins[$task->getOwner()->getPlugin()->name][] = $id;
            }
        }
        return $ids;
    }

    /**
     * Schedule a task
     * @param TaskInterface $task
     * @param $when
     * @return String
     */
    public function scheduleDelayedTask(TaskInterface $task, int $when) : String{
        $when = time() + $when;
        $this->tasks[$when][] = $task;
        $id = $when." ".(count($this->tasks[$when]) - 1);
        if($task instanceof PluginTask){
            $this->plugins[$task->getOwner()->getPlugin()->name][] = $id;
        }
        return $id;
    }

    public function call(int $time = -1){
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