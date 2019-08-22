<?php

namespace path\command\commands;

use path\command\PathPluginCommand;
use path\Main;
use path\Pos;
use path\world\pathfinding\PathNavigateGround;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

class Pos2Command extends PathPluginCommand{

    public function __construct(Plugin $plugin){
        parent::__construct('pos2', $plugin);
        $this->setAliases(['p2']);
        $this->setDescription('pos2 Command');
        $this->setUsage('/pos2');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!($sender instanceof Player)){
            $sender->sendMessage("このコマンドはゲーム内で実行してください");
            return true;
        }
        $sender->sendMessage("Pos2をセットしました");
        Main::getInstance()->navi[$sender->getName()] = new PathNavigateGround(new Pos($sender), $sender->getLevel());

        return true;
    }
}