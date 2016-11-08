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

namespace IRC\Tracking;

use IRC\Connection;
use IRC\Event\Channel\ChannelLeaveEvent;
use IRC\Event\Channel\JoinChannelEvent;
use IRC\Event\Channel\UserQuitEvent;
use IRC\Event\Kick\KickEvent;
use IRC\Event\Listener;
use IRC\Event\Message\MessageReceiveEvent;
use IRC\User;

class UserTracker implements Listener{

    private $connection;

    public function __construct(Connection $connection){
        $this->connection = $connection;
        $this->getConnection()->getEventHandler()->registerEvents($this);
    }

    public function getConnection(){
        return $this->connection;
    }

    public function onJoinChannelEvent(JoinChannelEvent $event){
        if($event->getUser()->getNick() === $this->connection->getNick() and !$this->connection->isInChannel($event->getChannel())){
            $this->connection->addChannel($event->getChannel()); // In case the server makes us join somewhere
        } elseif(!$event->getChannel()->hasUser($event->getUser()->getNick())){
            $event->getChannel()->addUser($event->getUser());
        }
    }

    public function onChannelLeaveEvent(ChannelLeaveEvent $event){
        if($event->getUser() instanceof User){
            if($event->getChannel()->hasUser($event->getUser()->getNick())){
                $event->getChannel()->removeUser($event->getUser()->getNick());
            }
        }
    }

    public function onMessageReceiveEvent(MessageReceiveEvent $event){
        if(!$event->getChannel()->hasUser($event->getUser()->getNick())){
            $event->getChannel()->addUser($event->getUser());
        }
    }

    public function onKickEvent(KickEvent $event){
        if($event->getUser() === $this->connection->getNick()){
            // The bot was kicked.
            $this->connection->removeChannel($event->getChannel());
        } else {
            // Someone else was kicked.
            $event->getChannel()->removeUser($event->getKicker());
        }
    }

    public function onUserQuitEvent(UserQuitEvent $event){
        foreach($this->getConnection()->getChannels() as $channel){
            if($channel->hasUser($event->getUser()->getNick())){
                $ev = new ChannelLeaveEvent($channel, $event->getUser());
                $this->getConnection()->getEventHandler()->callEvent($ev);
                if(!$ev->isCancelled()){
                    $channel->removeUser($event->getUser()->getNick());
                }
            }
        }
    }

}