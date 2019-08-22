<?php

namespace path\command\commands;

use path\command\PathPluginCommand;
use path\Main;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

class PosCommand extends PathPluginCommand{

    public function __construct(Plugin $plugin){
        parent::__construct('pos', $plugin);
        $this->setAliases(['p']);
        $this->setDescription('pos Command');
        $this->setUsage('/pos');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!($sender instanceof Player)){
            $sender->sendMessage("このコマンドはゲーム内で実行してください");
            return true;
        }
        $sender->sendMessage("Posをセットしました");
        Main::getInstance()->pos[$sender->getName()] = $sender->getPosition();

        return true;
    }
}