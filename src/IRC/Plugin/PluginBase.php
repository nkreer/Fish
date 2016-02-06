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

namespace IRC\Plugin;

use IRC\Connection;
use IRC\IRC;

class PluginBase{

    /**
     * @var Connection
     */
    public $connection;

    /**
     * @var Plugin
     */
    public $plugin;

    public function getConnection(){
        return $this->connection;
    }

    public function getClient(){
        return IRC::getInstance();
    }

    public function getPluginManager(){
        return $this->connection->getPluginManager();
    }

    public function getEventHandler(){
        return $this->connection->getEventHandler();
    }

    public function getScheduler(){
        return $this->connection->getScheduler();
    }

}