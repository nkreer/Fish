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

namespace IRC\Event\Command;

use IRC\Channel;
use IRC\Event\Event;
use IRC\User;

class CommandEvent extends Event{

    private $command;
    private $args;
    private $channel;
    private $user;

    public function __construct($command, $args, Channel $channel, User $user){
        $this->command = $command;
        $this->args = $args;
        $this->channel = $channel;
        $this->user = $user;
    }

    public function getCommand(){
        return $this->command;
    }

    public function getArgs(){
        return $this->args;
    }

    public function getChannel(){
        return $this->channel;
    }

    public function getUser(){
        return $this->user;
    }

}