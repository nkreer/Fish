<?php

namespace IRC\Event\Notice;

use IRC\Channel;

class NoticeSendEvent extends NoticeEvent{

    private $channel;

    public function __construct($notice, Channel $channel){
        parent::__construct($notice);
        $this->channel = $channel;
    }

    public function getChannel(){
        return $this->channel;
    }
    
}