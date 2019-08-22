<?php

namespace path\world\pathfinding;

class Path{

	private $pathPoints = [];
	private $count;

	public function addPoint($point){
		if($point->index >= 0){
		}else{
			if($this->count == count($this->pathPoints)){
				$apathpoint = [];
				for($i = 0; $i < $this->count; $i++){
					$apathpoint[$i] = $this->pathPoints[$i];
				}
				$this->pathPoints = $apathpoint;
			}

			$this->pathPoints[$this->count] = $point;
			$point->index = $this->count;
			$this->sortBack($this->count++);
			return $point;
		}
	}

	public function clearPath(){
		$this->count = 0;
	}

	public function dequeue(){
		$pathpoint = $this->pathPoints[0];
		$this->pathPoints[0] = $this->pathPoints[--$this->count];
		$this->pathPoints[$this->count] = null;

		if($this->count > 0){
			$this->sortForward(0);
		}

		$pathpoint->index = -1;
		return $pathpoint;
	}

	public function changeDistance($point, $distance){
		$f = $point->distanceToTarget;
		$point->distanceToTarget = $distance;

		if($distance < $f){
			$this->sortBack($point->index);
		}else{
			$this->sortForward($point->index);
		}
	}

	private function sortBack($aaaa){
		$pathpoint = $this->pathPoints[$aaaa];

		for($f = $pathpoint->distanceToTarget; $aaaa > 0; $aaaa = $i){
			$i = $aaaa - 1 >> 1;
			$pathpoint1 = $this->pathPoints[$i];

			if($f >= $pathpoint1->distanceToTarget){
				break;
			}

			$this->pathPoints[$aaaa] = $pathpoint1;
			$pathpoint1->index = $aaaa;
		}

		$this->pathPoints[$aaaa] = $pathpoint;
		$pathpoint->index = $aaaa;
	}

	private function sortForward($bbbb){
		$pathpoint = $this->pathPoints[$bbbb];
		$f = $pathpoint->distanceToTarget;

		while(true){
			$i = 1 + ($bbbb << 1);
			$j = $i + 1;

			if($i >= $this->count){
				break;
			}

			$pathpoint1 = $this->pathPoints[$i];
			$f1 = $pathpoint1->distanceToTarget;

			if($j >= $this->count){
				$pathpoint2 = null;
				$f2 = 2147483647;
			}else{
				$pathpoint2 = $this->pathPoints[$j];
				$f2 = $pathpoint2->distanceToTarget;
			}

			if($f1 < $f2){
				if ($f1 >= $f){
					break;
				}

				$this->pathPoints[$bbbb] = $pathpoint1;
				$pathpoint1->index = $bbbb;
				$bbbb = $i;
			}else{
				if($f2 >= $f){
					break;
				}

				$this->pathPoints[$bbbb] = $pathpoint2;
				$pathpoint2->index = $bbbb;
				$bbbb = $j;
			}
		}

		$this->pathPoints[$bbbb] = $pathpoint;
		$pathpoint->index = $bbbb;
	}

	public function isPathEmpty(){
		return $this->count == 0;
	}
}