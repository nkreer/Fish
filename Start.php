<?php

ini_set('memory_limit','4G');

if(!isset($argv[1])){
    die("Please supply an address to connect to.\n");
}

$address = $argv[1];
$port = (isset($argv[2]) ? $argv[2] : 6667); //Standard IRC port

includeFiles("IRC/");

$irc = new \IRC\IRC();
$irc->addConnection(new \IRC\Connection($address, $port));
$irc->run();

function includeFiles($baseDir){
    $dir = scandir($baseDir);
    foreach($dir as $element){
        if(is_file($baseDir.$element)){
            include_once($baseDir.$element);
        } elseif(is_dir($baseDir.$element) and $element != ".." and $element != "."){
            includeFiles($baseDir.$element."/");
        }
    }
}