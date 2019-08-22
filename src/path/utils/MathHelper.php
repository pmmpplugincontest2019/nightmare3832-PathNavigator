<?php

namespace path\utils;

class MathHelper{

	public static function floor_float(float $value) : int{
		$i = (int)$value;
		return $value < (float)$i ? $i - 1 : $i;
	}

	public static function ceiling_float_int(float $value) : int{
		$i = (int)$value;
		return $value > (float)$i ? $i + 1 : $i;
	}

	public static function clamp(float $num, float $min, float $max) : float{
		return $num < $min ? $min : ($num > $max ? $max : $num);
	}

	public static function wrapAngleTo180(float $value) : float{
		$value = $value % 360.0;

		if($value >= 180.0){
			$value -= 360.0;
		}

		if($value < -180.0){
			$value += 360.0;
		}

		return $value;
	}

	public static function nextGaussian(){
		$av = 0.0;
		$sd = 1.0;
		$x = mt_rand() / mt_getrandmax();
		$y = mt_rand() / mt_getrandmax();
		return sqrt(-2*log($x))*cos(2*pi()*$y)*$sd+$av;
	}
}