<?php

namespace path\world\pathfinding;

use pocketmine\block\Block;
use pocketmine\math\Vector3;

class PathNavigateGround extends PathNavigate{

	protected $nodeProcessor;

	public function __construct($entitylivingIn, $worldIn){
		parent::__construct($entitylivingIn, $worldIn);
	}

	protected function getPathFinder(){
		$this->nodeProcessor = new WalkNodeProcessor();
		$this->nodeProcessor->setEnterDoors(true);
		return new PathFinder($this->nodeProcessor);
	}

	protected function canNavigate(){
		return $this->theEntity->onGround || $this->getCanSwim() && $this->isInLiquid();
	}

	protected function getEntityPosition(){
		return new Vector3($this->theEntity->x, $this->getPathableY(), $this->theEntity->z);
	}

	private function getPathableY(){
		if($this->theEntity->isUnderwater() && $this->getCanSwim()){
			$i = (int)$this->theEntity->boundingBox->minY;
			$block = $this->level->getBlock(new Vector3(floor($this->theEntity->x), $i, floor($this->theEntity->z)));
			$j = 0;

			while($block->getId() == Block::WATER || $block->getId() == Block::STILL_WATER){
				++$i;
				$block = $this->level->getBlock(new Vector3(floor($this->theEntity->x), $i, floor($this->theEntity->z)));
				++$j;

				if($j > 16){
					return (int)$this->theEntity->boundingBox->minY;
				}
			}

			return $i;
		}else{
			return (int)($this->theEntity->boundingBox->minY);
		}
	}

	protected function isDirectPathBetweenPoints($posVec31, $posVec32, $sizeX, $sizeY, $sizeZ){
		$i = floor($posVec31->x);
		$j = floor($posVec31->z);
		$d0 = $posVec32->x - $posVec31->x;
		$d1 = $posVec32->z - $posVec31->z;
		$d2 = $d0 * $d0 + $d1 * $d1;

		if($d2 < 1.0E-8){
			return false;
		}else{
			$d3 = 1.0 / sqrt($d2);
			$d0 = $d0 * $d3;
			$d1 = $d1 * $d3;
			$sizeX = $sizeX + 2;
			$sizeZ = $sizeZ + 2;

			if(!$this->isSafeToStandAt($i, (int)$posVec31->y, $j, $sizeX, $sizeY, $sizeZ, $posVec31, $d0, $d1)){
				return false;
			}else{
				$sizeX = $sizeX - 2;
				$sizeZ = $sizeZ - 2;
				$d4 = 1.0 / abs($d0);
				$d5 = 1.0 / abs($d1);
				$d6 = ($i * 1) - $posVec31->x;
				$d7 = ($j * 1) - $posVec31->z;

				if($d0 >= 0.0){
					++$d6;
				}

				if($d1 >= 0.0){
					++$d7;
				}

				$d6 = $d6 / $d0;
				$d7 = $d7 / $d1;
				$k = $d0 < 0.0 ? -1 : 1;
				$l = $d1 < 0.0 ? -1 : 1;
				$i1 = floor($posVec32->x);
				$j1 = floor($posVec32->z);
				$k1 = $i1 - $i;
				$l1 = $j1 - $j;

				while($k1 * $k > 0 || $l1 * $l > 0){
					if($d6 < $d7){
						$d6 += $d4;
						$i += $k;
						$k1 = $i1 - $i;
					}else{
						$d7 += $d5;
						$j += $l;
						$l1 = $j1 - $j;
					}

					if(!$this->isSafeToStandAt($i, (int)$posVec31->y, $j, $sizeX, $sizeY, $sizeZ, $posVec31, $d0, $d1)){
						return false;
					}
				}

				return true;
			}
		}
	}

	private function isSafeToStandAt($x, $y, $z, $sizeX, $sizeY, $sizeZ, $vec31, $cccc, $dddd){
		$i = $x - $sizeX / 2;
		$j = $z - $sizeZ / 2;

		if(!$this->isPositionClear($i, $y, $j, $sizeX, $sizeY, $sizeZ, $vec31, $cccc, $dddd)){
			return false;
		}else{
			for($k = $i; $k < $i + $sizeX; ++$k){
				for($l = $j; $l < $j + $sizeZ; ++$l){
					$d0 = $k + 0.5 - $vec31->x;
					$d1 = $l + 0.5 - $vec31->z;

					if($d0 * $cccc + $d1 * $dddd >= 0.0){
						$block = $this->level->getBlock(new Vector3($k, $y - 1, $l));
						if($block->getId() == Block::AIR){
							return false;
						}

						if($block->getId() == Block::WATER && !$this->theEntity->isUnderwater()){
							return false;
						}

						if($block->getId() == Block::LAVA){
							return false;
						}
					}
				}
			}

			return true;
		}
	}

	private function isPositionClear($x, $y, $z, $sizeX, $sizeY, $sizeZ, $point, $eeee, $ffff){
		$blockpos = new Vector3();
		for($x1 = min($x, $x + $sizeX - 1); $x1 <= max($x, $x + $sizeX - 1); $x1++){
			for($y1 = min($y, $x + $sizeY - 1); $y1 <= max($y, $y + $sizeY - 1); $y1++){
				for($z1 = min($z, $z + $sizeZ - 1); $z1 <= max($z, $z + $sizeZ - 1); $z1++){
					$blockpos->setComponents($x1, $y1, $z1);
					$d0 = $blockpos->getX() + 0.5 - $point->x;
					$d1 = $blockpos->getZ() + 0.5 - $point->z;

					if($d0 * $eeee + $d1 * $ffff >= 0.0){
						$block = $this->level->getBlock($blockpos);

						if(!$block->isTransparent()){
							return false;
						}
					}
				}
			}
		}

		return true;
	}

	public function setAvoidsWater($avoidsWater){
		$this->nodeProcessor->setAvoidsWater($avoidsWater);
	}

	public function getAvoidsWater(){
		return $this->nodeProcessor->getAvoidsWater();
	}

	public function setBreakDoors($canBreakDoors){
		$this->nodeProcessor->setBreakDoors($canBreakDoors);
	}

	public function setEnterDoors($par1){
		$this->nodeProcessor->setEnterDoors($par1);
	}

	public function getEnterDoors(){
		return $this->nodeProcessor->getEnterDoors();
	}

	public function setCanSwim($canSwim){
		$this->nodeProcessor->setCanSwim($canSwim);
	}

	public function getCanSwim(){
		return $this->nodeProcessor->getCanSwim();
	}
}