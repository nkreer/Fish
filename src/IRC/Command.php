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

    public function __construct($command, $args){
        $this->command = $command;
        $this->args = $args;
    }

    /**
     * @return String
     */
    public function getCommand(){
        return $this->command;
    }

    /**
     * @return Array
     */
    public function getArgs(){
        return $this->args;
    }

    /**
     * @return Connection
     */
    public function getConnection(){
        return $this->connection;
    }

    /**
     * @param Connection $connection
     */
    public function setConnection(Connection $connection){
        $this->connection = $connection;
    }

}