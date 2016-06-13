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

class Event{

    private $cancelled = false;
    private $stop = false;

    /**
     * @return bool
     */
    public function isCancelled(){
        return $this->cancelled;
    }

    /**
     * @param bool|true $set
     */
    public function setCancelled($set = true){
        $this->cancelled = $set;
    }

    /**
     * Stop the event call
     */
    public function stopCall(){
        $this->stop = true;
    }

    /**
     * Check if the call has been stopped
     * @return bool
     */
    public function hasStopped(){
        return $this->stop;
    }

}