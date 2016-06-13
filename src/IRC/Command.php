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

class Command{

    private $command;
    private $args;
    private $connection;
    private $prefix;
    private $original;

    public function __construct(String $command, array $args, String $prefix = "", String $original = ""){
        $this->command = $command;
        $this->args = $args;
        $this->prefix = $prefix;
        $this->original = $original;
    }

    public function getOriginal() : String{
        return $this->original;
    }

    /**
     * @return String
     */
    public function getPrefix() : String{
        return $this->prefix;
    }

    /**
     * @return String
     */
    public function getCommand() : String{
        return $this->command;
    }

    /**
     * @return []
     */
    public function getArgs() : array{
        return $this->args;
    }

    /**
     * @param $arg
     * @return mixed
     */
    public function getArg($arg){
        return $this->args[$arg];
    }

    /**
     * @return Connection
     */
    public function getConnection() : Connection{
        return $this->connection;
    }

    /**
     * @param Connection $connection
     */
    public function setConnection(Connection $connection){
        $this->connection = $connection;
    }

}