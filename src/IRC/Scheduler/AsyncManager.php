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

class AsyncManager{

    /**
     * @var AsyncTask[]
     */
    private $tasks = [];

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection){
        $this->connection = $connection;
    }

    /*
     * Checking AsyncTasks for status
     */
    public function run(){
        foreach($this->getTasks() as $task){
            if(!$task->isRunning()){
                $task->onComplete($this->connection);
                $this->removeTask($task);
            } elseif($task->isTerminated()){
                throw new \RuntimeException("AsyncTask ".(new \ReflectionClass($task))->getShortName()." was terminated.");
            }
        }
    }

    /**
     * @param AsyncTask $task
     */
    public function addTask(AsyncTask $task){
        $this->tasks[$task->getCurrentThreadId()] = $task;
    }

    /**
     * @param AsyncTask $task
     * @return bool
     */
    public function removeTask(AsyncTask $task) : bool{
        if($this->hasTask($task->getCurrentThreadId())){
            unset($this->tasks[$task->getCurrentThreadId()]);
            return true;
        }
        return false;
    }

    /**
     * @param $threadId
     * @return bool
     */
    public function hasTask($threadId) : bool{
        return isset($this->tasks[$threadId]);
    }

    /**
     * @return AsyncTask[]
     */
    public function getTasks() : array{
        return $this->tasks;
    }

}