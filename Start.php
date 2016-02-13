<?php

if(!isset($argv[1])){
    die("Please supply an address to connect to.\n");
}

$address = $argv[1];
$port = (isset($argv[2]) ? $argv[2] : 6667); //Standard IRC port

spl_autoload_extensions(".php");
spl_autoload_register();

$irc = new \IRC\IRC();
$irc->addConnection(new \IRC\Connection($address, $port));
$irc->run();