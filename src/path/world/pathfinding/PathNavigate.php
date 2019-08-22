<?php

namespace path\world\pathfinding;

use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\level\particle\LavaDripParticle;

abstract class PathNavigate{

	protected $theEntity;
	protected $level;
	protected $currentPath;
	private $pathSearchRange;
	private $totalTicks;
	private $ticksAtLastPos;
	private $lastPosCheck;
	private $heightRequirement = 1.0;
	private $pathFinder;

	public function __construct($entitylivingIn, $worldIn){
		$this->theEntity = $entitylivingIn;
		$this->level = $worldIn;
		$this->pathSearchRange = 50;
		$this->pathFinder = $this->getPathFinder();
		$this->lastPosCheck = new Vector3();
	}

	protected abstract function getPathFinder();

	public function getPathSearchRange(){
		return $this->pathSearchRange;
	}

	public function getPathToXYZ($x, $y, $z){
		return $this->getPathToPos(new Vector3(floor($x), (int)$y, floor($z)));
	}

	public function getPathToPos($pos){
		if(!$this->canNavigate()){
			return null;
		}else{
			$f = $this->getPathSearchRange();
			$pathentity = $this->pathFinder->createEntityPathTo2($this->level, $this->theEntity, $pos, $f);
			return $pathentity;
		}
	}

	public function tryMoveToXYZ($x, $y, $z){
		$pathentity = $this->getPathToXYZ(floor($x), (int)$y, floor($z));
		return $this->setPath($pathentity);
	}

	public function setHeightRequirement($jumpHeight){
		$this->heightRequirement = $jumpHeight;
	}

	public function setPath($pathentityIn){
		if($pathentityIn == null){
			$this->currentPath = null;
			return false;
		}else{
			if(!$pathentityIn->isSamePath($this->currentPath)){
				$this->currentPath = $pathentityIn;
			}

			if($this->currentPath->getCurrentPathLength() == 0){
				return false;
			}else{
				$vec3 = $this->getEntityPosition();
				$this->ticksAtLastPos = $this->totalTicks;
				$this->lastPosCheck = $vec3;
				return true;
			}
		}
	}

	public function getPath(){
		return $this->currentPath;
	}

	public function onUpdateNavigation(){
		++$this->totalTicks;

		if(!$this->noPath()){
			if($this->canNavigate()){
				$this->pathFollow();
			}else if($this->currentPath != null && $this->currentPath->getCurrentPathIndex() < $this->currentPath->getCurrentPathLength()){
				$vec3 = $this->getEntityPosition();
				$vec31 = $this->currentPath->getVectorFromIndex($this->theEntity, $this->currentPath->getCurrentPathIndex());

 				if($vec3->y > $vec31->y && !$this->theEntity->onGround && floor($vec3->x) == floor($vec31->x) && floor($vec3->z) == floor($vec31->z)){
					$this->currentPath->setCurrentPathIndex($this->currentPath->getCurrentPathIndex() + 1);
				}
			}
		}
	}

	protected function pathFollow(){
		for($j = 0/*$this->currentPath->getCurrentPathIndex()*/; $j < $this->currentPath->getCurrentPathLength(); ++$j){
			$c = $this->currentPath->getPathPointFromIndex($j);
			$this->theEntity->level->addParticle(new LavaDripParticle(new Vector3($c->xCoord + 0.5, $c->yCoord + 1, $c->zCoord + 0.5)));
		}
	}

	public function noPath(){
		return $this->currentPath == null || $this->currentPath->isFinished();
	}

	public function clearPathEntity(){
		$this->currentPath = null;
	}

	protected abstract function getEntityPosition();

	protected abstract function canNavigate();

	protected function isInLiquid(){
		return $this->theEntity->isUnderwater();// || $this->theEntity->isInLava();
	}

	protected function removeSunnyPath(){
	}

	protected abstract function isDirectPathBetweenPoints($posVec31, $posVec32, $sizeX, $sizeY, $sizeZ);
}