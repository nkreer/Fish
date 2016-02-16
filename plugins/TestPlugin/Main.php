<?php

namespace TestPlugin;

use IRC\Event\Command\CommandEvent;
use IRC\Event\Listener;
use IRC\Plugin\PluginBase;

class Main extends PluginBase implements Listener{

    public function onLoad(){
        //Code on plugin load
        //Don't forget to set "load" to true in the plugin.json
        include_once("TestTask.php");
        $this->getEventHandler()->registerEvents($this, $this->plugin); //Register a class to the event handler
        $this->getScheduler()->scheduleDelayedTask(new TestTask(), 60); //Register a new task
    }

    public function onDisable(){
        //Code on plugin unload
    }

    //Command-Event
    //Events are always defined as 'on'.eventName(EventName $event)
    public function onCommandEvent(CommandEvent $event){
        //Code on CommandEvent
    }

}
