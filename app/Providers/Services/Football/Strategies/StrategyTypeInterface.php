<?php

namespace App\Providers\Services\Football\Strategies;


interface StrategyTypeInterface {

	public function getResult($type = 'odds');
}