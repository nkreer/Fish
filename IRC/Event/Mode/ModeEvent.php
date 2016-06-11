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

namespace IRC\Event\Mode;

use IRC\Channel;
use IRC\Event\Event;
use IRC\User;

class ModeEvent extends Event{

	private $mode;
	private $by;
	private $channel;

	public function __construct($mode, User $by, Channel $channel){
		$this->mode = $mode;
		$this->by = $by;
		$this->channel = $channel;
	}

	public function getChannel(){
		return $this->channel;
	}

	public function getChanger(){
		return $this->by;
	}

	public function getMode(){
		return $this->mode;
	}

}