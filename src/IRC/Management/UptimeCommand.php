<?php

/*
 *
 * Fish - IRC Bot
 * Copyright (C) 2017 Niklas Kreer
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

namespace IRC\Management;

use IRC\Command\Command;
use IRC\Command\CommandExecutor;
use IRC\Command\CommandInterface;
use IRC\Command\CommandSender;
use IRC\Connection;
use IRC\IRC;

class UptimeCommand extends Command implements CommandExecutor{

    private $connection;

    public function __construct(Connection $connection){
        $this->connection = $connection;
        parent::__construct("uptime", $this, "fish.management.uptime", "Display the uptime", "uptime");
    }

    public function onCommand(CommandInterface $command, CommandSender $sender, CommandSender $room, array $args){
        $time = new \DateTime(date("d.m.Y H:i:s", time()));
        $other = new \DateTime(date("d.m.Y H:i:s", IRC::getInstance()->getStartupTime()));
        $diff = $time->diff($other);
        $uptime = $diff->y." years, ";
        $uptime .= $diff->m." months, ";
        $uptime .= $diff->d." days, ";
        $uptime .= $diff->h." hours, ";
        $uptime .= $diff->i." minutes and ";
        $uptime .= $diff->s." seconds";
        $uptime .= ", which makes a total of ".$diff->days." days";
        $sender->sendNotice("Uptime: ".$uptime);
    }

}