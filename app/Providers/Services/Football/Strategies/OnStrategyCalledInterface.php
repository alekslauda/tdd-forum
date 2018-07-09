<?php

namespace App\Providers\Services\Football\Strategies;


interface OnStrategyCalledInterface {

	public function onStrategyCalled( $key, $f1, $f2, $args = [] );
}