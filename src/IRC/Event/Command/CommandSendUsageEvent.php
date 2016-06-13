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

use IRC\Command\Command;
use IRC\Command\CommandSender;
use IRC\Event\Event;

class CommandSendUsageEvent extends Event{

    private $command;
    private $sender;
    private $room;
    private $args = [];

    public function __construct(Command $command, CommandSender $sender, CommandSender $room, array $args){
        $this->command = $command;
        $this->sender = $sender;
        $this->room = $room;
        $this->args = $args;
    }

    public function getCommand() : Command{
        return $this->command;
    }

    public function getSender() : CommandSender{
        return $this->sender;
    }

    public function getRoom() : CommandSender{
        return $this->room;
    }

    public function getArgs() : array{
        return $this->args;
    }

}