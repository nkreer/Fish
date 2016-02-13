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

namespace IRC;

class Parser{

    public static function parse($command){
        if (substr($command, 0, 1) == ":"){
            $prefix = substr($command, 1, strpos($command, " "));
            $command = substr($command, strpos($command, " ") + 1);
        } else {
            $prefix = false;
        }

        $cmd = substr($command, 0, strpos($command, " "));
        $cmd = strtoupper($cmd);
        $args = str_replace("\r", "", substr($command, strpos($command, " ") + 1));
        $args = explode(" ", $args);

        return new Command($cmd, $args, $prefix);
    }

}