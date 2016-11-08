<?php
//Setup
ini_set('memory_limit', '4G');

if(!isset($argv[1])){
    die("Please supply an address to connect to.\n");
}

include_once("vendor/autoload.php");

$address = $argv[1];

for($i = 0; $i < 2; $i++) unset($argv[$i]);
$message = implode(" ", $argv);
$args = \IRC\Utils\ArgumentParser::parse($message, ["password", "no-ssl", "config", "port"]);

$port = (!empty($args["port"]) ? $args["port"] : \IRC\IRC::IRC_PORT_ENCRYPTED);
$address = (empty($args["no-ssl"]) ? "ssl://".$address : $address);
$password = (!empty($args["password"]) ? $args["password"] : false);
$config = (!empty($args["config"]) ? $args["config"] : "fish.json");

$irc = new \IRC\IRC(false, false, $config);
$irc->addConnection(new \IRC\Connection($address, $port, $password));
$irc->run();