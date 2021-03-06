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

use IRC\Channel;
use IRC\Command\Command;
use IRC\Command\CommandExecutor;
use IRC\Command\CommandInterface;
use IRC\Command\CommandSender;
use IRC\Connection;

class SayCommand extends Command implements CommandExecutor{

    private $connection;

    public function __construct(Connection $connection){
        $this->connection = $connection;
        parent::__construct("say", $this, "fish.management.say", "Send a message to a channel", "say <#channel> <text>");
        $this->addAlias("echo");
        $this->addAlias("msg");
    }

    public function onCommand(CommandInterface $command, CommandSender $sender, CommandSender $room, array $args){
        if(!empty($args[1])){
            $channel = Channel::getChannel($this->connection, $args[1]);
            unset($args[1]);
            $text = implode(" ", $args);
            if(!empty($text)){
                if($text[0] === "*"){
                    $channel->sendAction(str_replace("*", "", $text));
                } else {
                    $channel->sendMessage($text);
                }
                $sender->sendNotice("Message sent.");
            } else {
                $sender->sendNotice("No text to say");
            }
        } else {
            $sender->sendNotice("Argument 1 must be a channel");
        }
    }

}