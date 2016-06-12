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

namespace IRC\Event\Nick;

use IRC\User;

class UserChangeNickEvent extends NickEvent{

	private $user;
	private $nickOld;
	private $nickNew;

	public function __construct(User $user, $nickBefore, $nickNew){
		parent::__construct($nickBefore);
		$this->nickOld = $nickBefore;
		$this->nickNew = $nickNew;
		$this->user = $user;
	}

	public function getOldNick(){
		return $this->nickOld;
	}

	public function getNewNick(){
		return $this->nickNew;
	}

	public function getUser(){
		return $this->user;
	}

}