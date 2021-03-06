<?php

/*
 *
 * Fish - IRC Bot
 * Copyright (C) 2016 - 2017 Niklas Kreer
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

namespace IRC\Management;

use IRC\Connection;

class ManagementCommands{

    public function __construct(Connection $connection){
        $connection->getCommandMap()->registerCommand(new JoinCommand($connection));
        $connection->getCommandMap()->registerCommand(new PartCommand($connection));
        $connection->getCommandMap()->registerCommand(new HelpCommand($connection));
        $connection->getCommandMap()->registerCommand(new PluginLoadCommand($connection));
        $connection->getCommandMap()->registerCommand(new PluginUnloadCommand($connection));
        $connection->getCommandMap()->registerCommand(new PluginReloadCommand($connection));
        $connection->getCommandMap()->registerCommand(new PluginsListCommand($connection));
        $connection->getCommandMap()->registerCommand(new WhoamiCommand($connection));
        $connection->getCommandMap()->registerCommand(new RawCommand($connection));
        $connection->getCommandMap()->registerCommand(new SayCommand($connection));
        $connection->getCommandMap()->registerCommand(new NickCommand($connection));
        $connection->getCommandMap()->registerCommand(new ReloadCommand($connection));
        $connection->getCommandMap()->registerCommand(new StopCommand($connection));
        $connection->getCommandMap()->registerCommand(new UptimeCommand($connection));
    }

}