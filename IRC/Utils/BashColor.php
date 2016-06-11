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

namespace IRC\Utils;

class BashColor{

	const RED = "\033[0;31m";
	const YELLOW = "\033[1;33m";
	const GREEN = "\033[1;32m";
	const BLUE = "\033[1;34m";
	const CYAN = "\033[1;36m";
	const PURPLE = "\033[0;35m";
	const WHITE = "\033[1;37m";
	const BLACK = "\033[0;30m";
	const REMOVE = "\033[0m";
	const HIGHLIGHT = "\033[44m";

}