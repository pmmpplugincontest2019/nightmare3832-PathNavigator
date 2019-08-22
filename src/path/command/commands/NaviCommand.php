<?php

namespace path\command\commands;

use path\command\PathPluginCommand;
use path\Main;
use path\world\pathfinding\PathNavigateGround;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

class NaviCommand extends PathPluginCommand{

    public function __construct(Plugin $plugin){
        parent::__construct('navi', $plugin);
        $this->setAliases(['n']);
        $this->setDescription('navi Command');
        $this->setUsage('/navi');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!($sender instanceof Player)){
            $sender->sendMessage("このコマンドはゲーム内で実行してください");
            return true;
        }
        if(!$sender->isOp()) return false;
        Main::getInstance()->on[$sender->getName()] = !Main::getInstance()->on[$sender->getName()];

        return true;
    }
}