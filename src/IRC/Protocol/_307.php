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

namespace IRC\Protocol;

use IRC\Authentication\AuthenticationStatus;
use IRC\Authentication\UpdateAuthenticationStatusTask;
use IRC\Command;
use IRC\Connection;
use IRC\Event\Identify\UserIdentifyEvent;
use IRC\IRC;
use IRC\User;
use IRC\Utils\JsonConfig;

/**
 * User Identification
 * Class _307
 * @package IRC\Protocol
 */
class _307 implements ProtocolCommand{

    public static function run(Command $command, Connection $connection, JsonConfig $config){
        $user = User::getUserByNick($connection, $command->getArg(1));
        if($user instanceof User){
            $ev = new UserIdentifyEvent($user);
            $connection->getEventHandler()->callEvent($ev);
            if(!$ev->isCancelled()){
                if($user->identified === AuthenticationStatus::UNCHECKED){
                    $config = IRC::getInstance()->getConfig();
                    if($config->getData("authentication_message")["enabled"] === true){
                        $user->sendNotice($config->getData("authentication_message")["message"]);
                    }
                }
                $user->identified = AuthenticationStatus::IDENTIFIED;
                $connection->getScheduler()->scheduleDelayedTask(new UpdateAuthenticationStatusTask($user, $connection), $config->getData("authentication_ttl"));
            }
        }
    }

}