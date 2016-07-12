<?php
//Setup
ini_set('memory_limit', '4G');

if(!isset($argv[1])){
    die("Please supply an address to connect to.\n");
}

if(!extension_loaded("pthreads")){
    die("Please install the pthreads extension.\n");
}

include_once("vendor/autoload.php");

$address = $argv[1];
$port = (isset($argv[2]) ? $argv[2] : 6697); //Standard IRC port, encrypted

for($i = 0; $i < 2; $i++) unset($argv[$i]);
$message = implode(" ", $argv);
$args = \IRC\Utils\ArgumentParser::parse($message, ["password", "insecure"]);

$address = (empty($args["insecure"]) ? "ssl://".$address : $address);
$password = (!empty($args["password"]) ? $args["password"] : false);

$irc = new \IRC\IRC(false);
$irc->addConnection(new \IRC\Connection($address, $port, $password));
$irc->run();