<?php

namespace IRC\Event\Notice;

use IRC\Event\Event;

class NoticeEvent extends Event{

    private $notice = "";

    public function __construct($notice){
        $this->notice = $notice;
    }

    /**
     * @param $notice
     */
    public function setNotice($notice){
        $this->notice = $notice;
    }

    /**
     * @return string
     */
    public function getNotice(){
        return $this->notice;
    }
    
}