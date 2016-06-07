<?php

namespace IRC\Protocol;

use IRC\Command;
use IRC\Connection;
use IRC\Utils\JsonConfig;

interface ProtocolCommand{

    public static function run(Command $command, Connection $connection, JsonConfig $config);
    
}