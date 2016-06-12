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

namespace IRC\Event;

use IRC\Plugin\Plugin;

class EventHandler{

	private $listeners = [];

	/**
	 * @param Listener $listener
	 * @param Plugin|null $plugin
	 * @param int $priority
	 */
	public function registerEvents(Listener $listener, Plugin $plugin = null, int $priority = EventPriority::LOWEST){
		if($plugin instanceof Plugin){
			$this->listeners[$priority][$plugin->name][] = $listener;
		} else{
			$this->listeners[$priority][] = $listener;
		}
		ksort($this->listeners);
	}

	/**
	 * Unregister all listeners
	 */
	public function unregisterAll(){
		$this->listeners = [];
	}

	/**
	 * @param Plugin $plugin
	 */
	public function unregisterPlugin(Plugin $plugin, int $priority = EventPriority::LOWEST){
		unset($this->listeners[$priority][$plugin->name]);
	}

	/**
	 * @param Event $event
	 */
	public function callEvent(Event $event){
		foreach($this->listeners as $priority){
			foreach($priority as $listener){
				if($listener instanceof Listener){
					$this->runEvent($event, $listener);
				} else{
					foreach($listener as $plugin){
						$this->runEvent($event, $plugin);
					}
				}
				if($event->hasStopped()){
					break;
				}
			}
			if($event->hasStopped()){
				break;
			}
		}
	}

	/**
	 * @param Event $event
	 * @param Listener $listener
	 */
	private function runEvent(Event $event, Listener $listener){
		$eventClass = new \ReflectionClass($event);
		$reflectionClass = new \ReflectionClass($listener);
		$eventName = "on".$eventClass->getShortName();
		$eventGroupName = "on".$eventClass->getParentClass()->getShortName();
		if($reflectionClass->hasMethod($eventName)){
			$listener->$eventName($event);
		} elseif($reflectionClass->hasMethod("on".$eventGroupName)){
			$listener->$eventGroupName($event);
		}
	}

}