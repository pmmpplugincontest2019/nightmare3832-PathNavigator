<?php

namespace path\command;

use pocketmine\command\PluginCommand;
use pocketmine\plugin\Plugin;

abstract class PathPluginCommand extends PluginCommand{
    public function __construct($name, Plugin $owner){
        parent::__construct($name, $owner);
    }
}
