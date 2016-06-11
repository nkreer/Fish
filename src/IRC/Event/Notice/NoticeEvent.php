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

namespace IRC\Event\Notice;

use IRC\Event\Event;

class NoticeEvent extends Event{

	private $notice = "";

	public function __construct($notice){
		$this->notice = $notice;
	}

	/**
	 * @return string
	 */
	public function getNotice(){
		return $this->notice;
	}

	/**
	 * @param $notice
	 */
	public function setNotice($notice){
		$this->notice = $notice;
	}

}