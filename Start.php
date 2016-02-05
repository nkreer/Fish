<?php

includeFiles("src/IRC/", true);

$irc = new \IRC\IRC();
$irc->addConnection(new \IRC\Connection($argv[1], $argv[2]));
$irc->run();

function includeFiles($baseDir, $debug = true){
    $dir = scandir($baseDir);
    foreach($dir as $element){
        if(is_file($baseDir.$element)){
            if($debug === true){
                echo "Load ".$baseDir.$element."\n";
            }
            include_once($baseDir.$element);
        } elseif(is_dir($baseDir.$element) and $element != ".." and $element != "."){
            includeFiles($baseDir.$element."/", $debug);
        }
    }
}