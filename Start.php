<?php

ini_set('memory_limit','4G');

if(!isset($argv[1])){
    die("Please supply an address to connect to.\n");
}

$address = (empty($argv[3]) ? "ssl://".$argv[1] : $argv[1]);
$port = (isset($argv[2]) ? $argv[2] : 6697); //Standard IRC port, encrypted

include_once("vendor/autoload.php");

$irc = new \IRC\IRC();
$irc->addConnection(new \IRC\Connection($address, $port));
$irc->run();