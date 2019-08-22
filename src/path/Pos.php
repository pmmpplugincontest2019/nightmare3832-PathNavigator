<?php

namespace path;

use pocketmine\Player;

class Pos{

    public $level;
    public $boundingBox;
    public $x;
    public $y;
    public $z;
    public $width;
    public $height;
    public $isUnderwater;

    public $onGround;

    public function __construct(Player $player){
        $this->level = $player->level;
        $this->boundingBox = clone $player->boundingBox;
        $this->x = $player->x;
        $this->y = $player->y;
        $this->z = $player->z;
        $this->width = $player->width;
        $this->height = $player->height;
        $this->isUnderwater = $player->isUnderwater();
        $this->onGround = $player->onGround;
    }

    public function isUnderwater() : bool{
        return $this->isUnderwater;
    }

}