<?php

namespace IRC\Event\Command;

use IRC\Event\Event;

class CommandLineEvent extends Event{

    private $message;

    public function __construct($message){
        $this->message = $message;
    }

    public function getMessage(){
        return $this->message;
    }

}