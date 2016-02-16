<?php

namespace TestPlugin;

use IRC\Logger;
use IRC\Scheduler\TaskInterface;

class TestTask implements TaskInterface{

    public function onRun(){
        //Code on Task execution
        Logger::info("This is an example message.");
    }

}