<?php

namespace App\Providers\Services\Football\Strategies;

class BothTeamsToScore extends Base implements onStrategyCalledInterface  {
	public static $name = 'both.teams.to.score';

	protected $possibility = 0;
}