<?php

namespace App\Providers\Services\Football\Strategies;


class Base implements StrategyTypeInterface, onStrategyCalledInterface {

	protected function calcPossibility($fixture1, $fixture2)
	{
		$fix1 = (double) $fixture1;
		$fix2 = (double) $fixture2;
		return $fix1/100 * $fix2/100;
	}

	public function onStrategyCalled( $key, $f1, $f2, $args = [] )
	{
		if ( static::$name != $key ) return false;

		$this->possibility += $this->calcPossibility($f1, $f2);
		
		return true;
	}

	protected function convert($type = 'odds')
	{
		$possibility = (double) $this->possibility;

		if ($type === 'percentage') {
			$possibility = round((1/(1/$possibility))*100, 2);
		} elseif($type === 'odds') {
			$possibility = round((1/$possibility), 2);
		}
		
		return $possibility;
	}

	public function getResult($type = 'odds')
	{
		return $this->convert($type);
	}
}