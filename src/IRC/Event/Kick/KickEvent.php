<?php

namespace IRC\Event\Kick;

use IRC\Channel;
use IRC\Event\Event;
use IRC\User;

class KickEvent extends Event{

    private $channel;
    private $user;
    private $kicker;

    public function __construct(User $user, Channel $channel, User $kicker){
        $this->user = $user;
        $this->channel = $channel;
        $this->kicker = $kicker;
    }

    public function getKicker(){
        return $this->kicker;
    }
    
    public function getUser(){
        return $this->user;
    }

    public function getChannel(){
        return $this->channel;
    }

}