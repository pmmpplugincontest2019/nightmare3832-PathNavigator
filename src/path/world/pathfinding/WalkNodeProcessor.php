<?php

namespace path\world\pathfinding;

use pocketmine\block\Block;
use pocketmine\block\CobblestoneWall;
use pocketmine\block\Door;
use pocketmine\block\Fence;
use pocketmine\block\FenceGate;
use pocketmine\block\Flowable;
use pocketmine\block\Rail;
use pocketmine\math\Vector3;

class WalkNodeProcessor extends NodeProcessor{

	private $canEnterDoors;
	private $canBreakDoors;
	private $avoidsWater;
	private $canSwim;
	private $shouldAvoidWater;

	public function initProcessor($iblockaccessIn, $entityIn){
		parent::initProcessor($iblockaccessIn, $entityIn);
		$this->shouldAvoidWater = $this->avoidsWater;
	}

	public function postProcess(){
		parent::postProcess();
		$this->avoidsWater = $this->shouldAvoidWater;
	}

	public function getPathPointTo($entityIn){
		if($this->canSwim && $entityIn->isUnderwater()){
			$i = (int)$entityIn->boundingBox->minY;
			$mutableblockpos = new Vector3(floor($entityIn->x), $i, floor($entityIn->z));

			for($block = $this->level->getBlock($mutableblockpos); $block->getId() == Block::STILL_WATER || $block->getId() == Block::WATER; $block = $this->level->getBlock($mutableblockpos)){
				++$i;
				$mutableblockpos->setComponents(floor($entityIn->x), $i, floor($entityIn->z));
			}

			$this->avoidsWater = false;
		}else{
			$i = floor($entityIn->boundingBox->minY);
		}

		return $this->openPoint(floor($entityIn->boundingBox->minX), $i, floor($entityIn->boundingBox->minZ));
	}

	public function getPathPointToCoords($entityIn, $x, $y, $target){
		return $this->openPoint(floor($x - ($entityIn->width / 2.0)), floor($y), floor($target - ($entityIn->width / 2.0)));
	}

	public function findPathOptions($pathOptions, $entityIn, $currentPoint, $targetPoint, $maxDistance){
		$i = 0;
		$j = 0;

		if($this->getVerticalOffset($entityIn, $currentPoint->xCoord, $currentPoint->yCoord + 1, $currentPoint->zCoord) == 1){
			$j = 1;
		}

		$pathpoint = $this->getSafePoint($entityIn, $currentPoint->xCoord, $currentPoint->yCoord, $currentPoint->zCoord + 1, $j);
		$pathpoint1 = $this->getSafePoint($entityIn, $currentPoint->xCoord - 1, $currentPoint->yCoord, $currentPoint->zCoord, $j);
		$pathpoint2 = $this->getSafePoint($entityIn, $currentPoint->xCoord + 1, $currentPoint->yCoord, $currentPoint->zCoord, $j);
		$pathpoint3 = $this->getSafePoint($entityIn, $currentPoint->xCoord, $currentPoint->yCoord, $currentPoint->zCoord - 1, $j);

		if($pathpoint != null && !$pathpoint->visited && $pathpoint->distanceTo($targetPoint) < $maxDistance){
			$pathOptions[$i++] = $pathpoint;
		}

		if($pathpoint1 != null && !$pathpoint1->visited && $pathpoint1->distanceTo($targetPoint) < $maxDistance){
			$pathOptions[$i++] = $pathpoint1;
		}

		if($pathpoint2 != null && !$pathpoint2->visited && $pathpoint2->distanceTo($targetPoint) < $maxDistance){
			$pathOptions[$i++] = $pathpoint2;
		}

		if($pathpoint3 != null && !$pathpoint3->visited && $pathpoint3->distanceTo($targetPoint) < $maxDistance){
			$pathOptions[$i++] = $pathpoint3;
		}

		return [$i, $pathOptions];
	}

	private function getSafePoint($entityIn, $x, $y, $z, $p_176171_5_){
		$pathpoint = null;
		$i = $this->getVerticalOffset($entityIn, $x, $y, $z);

		if($i == 2){
			return $this->openPoint($x, $y, $z);
		}else{
			if($i == 1){
				$pathpoint = $this->openPoint($x, $y, $z);
			}

			if($pathpoint === null && $p_176171_5_ > 0 && $i != -3 && $i != -4 && $this->getVerticalOffset($entityIn, $x, $y + $p_176171_5_, $z) == 1){
				$pathpoint = $this->openPoint($x, $y + $p_176171_5_, $z);
				$y += $p_176171_5_;
			}

			if($pathpoint != null){
				$j = 0;

				for($k = 0; $y > 0; $pathpoint = $this->openPoint($x, $y, $z)){
					$k = $this->getVerticalOffset($entityIn, $x, $y - 1, $z);

					if($this->avoidsWater && $k == -1){
						return null;
					}

					if($k != 1){
						break;
					}

					if($j++ >= 3){
						return null;
					}

					--$y;

					if($y <= 0){
						return null;
					}
				}

				if($k == -2){
					return null;
				}
			}

			return $pathpoint;
		}
	}

	private function getVerticalOffset($entityIn, $x, $y, $z){
		return self::check($this->level, $entityIn, $x, $y, $z, $this->entitySizeX, $this->entitySizeY, $this->entitySizeZ, $this->avoidsWater, $this->canBreakDoors, $this->canEnterDoors);
	}

	public static function check($level, $entityIn, $x, $y, $z, $sizeX, $sizeY, $sizeZ, $avoidWater, $breakDoors, $enterDoors){
		$flag = false;
		$blockpos = new Vector3($entityIn->x, $entityIn->y, $entityIn->z);
		$mutableblockpos = new Vector3();

		for($i = $x; $i < $x + $sizeX; ++$i){
			for($j = $y; $j < $y + $sizeY; ++$j){
				for($k = $z; $k < $z + $sizeZ; ++$k){
					$mutableblockpos->setComponents($i, $j, $k);
					$block = $level->getBlock($mutableblockpos);

					if($block->getId() != Block::AIR && !($block instanceof Flowable)){
						if($block->getId() != Block::TRAPDOOR && $block->getId() != Block::IRON_TRAPDOOR){
							if($block->getId() != Block::STILL_WATER && $block->getId() != Block::WATER){
								if(!$enterDoors && $block instanceof Door/*wood*/){
									return 0;
								}
							}else{
								if($avoidWater){
									return -1;
								}

								$flag = true;
							}
						}else{
							$flag = true;
						}

						if(!$block->canPassThrough() && (!$breakDoors || !($block instanceof Door)/* || block.getMaterial() != Material.wood*/)){
							if($block instanceof Fence || $block instanceof FenceGate || $block instanceof CobblestoneWall){
								return -3;
							}

							if($block->getId() == Block::TRAPDOOR || $block->getId() == Block::IRON_TRAPDOOR){
								return -4;
							}

							if($block->getId() != Block::LAVA && $block->getId() != Block::STILL_LAVA){
								return 0;
							}

							//if(!$entityIn->isInLava()){
							//	return -2;
							//}
						}
					}
				}
			}
		}

		return $flag ? 2 : 1;
	}

	public function setEnterDoors($canEnterDoorsIn){
		$this->canEnterDoors = $canEnterDoorsIn;
	}

	public function setBreakDoors($canBreakDoorsIn){
		$this->canBreakDoors = $canBreakDoorsIn;
	}

	public function setAvoidsWater($avoidsWaterIn){
		$this->avoidsWater = $avoidsWaterIn;
	}

	public function setCanSwim($canSwimIn){
		$this->canSwim = $canSwimIn;
	}

	public function getEnterDoors(){
		return $this->canEnterDoors;
	}

	public function getCanSwim(){
		return $this->canSwim;
	}

	public function getAvoidsWater(){
		return $this->avoidsWater;
	}
}