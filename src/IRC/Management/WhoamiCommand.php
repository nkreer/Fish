<?php

namespace IRC\Management;

use IRC\Command\Command;
use IRC\Command\CommandExecutor;
use IRC\Command\CommandInterface;
use IRC\Command\CommandSender;
use IRC\User;

class WhoamiCommand extends Command implements CommandExecutor{

    public function __construct(){
        parent::__construct("whoami", $this, false, "Get information about you", "whoami");
    }

    public function onCommand(CommandInterface $command, CommandSender $sender, CommandSender $room, array $args){
        if($sender instanceof User){
            $sender->sendNotice("Authentication status: ".$sender->identified." | Operator: ".($sender->isOperator() ? "yes" : "no"));
        }
        return true;
    }

}