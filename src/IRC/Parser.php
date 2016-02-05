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
        $line = explode(":", $command, 2);
        $cmd = explode(" ", $line[0]);
        $command = $cmd[0];
        unset($cmd[0]);
        if(isset($line[1])){
            $cmd[] = $line[1];
        }
        array_values($cmd);

        return new Command($command, $line[0]);
    }

}