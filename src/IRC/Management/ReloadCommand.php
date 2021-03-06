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

namespace IRC\Management;

use IRC\Command\Command;
use IRC\Command\CommandExecutor;
use IRC\Command\CommandInterface;
use IRC\Command\CommandSender;
use IRC\IRC;

class ReloadCommand extends Command implements CommandExecutor{

    public function __construct(){
        parent::__construct("reload", $this, "fish.management.reload", "Reload everything", "reload");
    }

    public function onCommand(CommandInterface $command, CommandSender $sender, CommandSender $room, array $args){
        $sender->sendNotice("Reloading...");
        IRC::getInstance()->reload();
        $sender->sendNotice("Reload complete.");
    }

}