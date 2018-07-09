<?php

namespace App\Providers\Services\Football\Strategies;

class CorrectScores extends Base {


	public static $name = 'correct.scores';

	protected $_scores = [];

	public function onStrategyCalled( $key, $f1, $f2, $args = [] )
	{
		if ( self::$name != $key ) return false;
		
		$score = new Score($args['res'], $f1, $f2);
		$this->_scores[$score->getCorrectScore()] = $score;

		return true;
	}

	public function getResult($type = 'odds')
	{
		foreach($this->_scores as $res => $score) {
		 	$score->setPossibilityType($type);
			$this->_scores[$res] = $score;
		}

		return $this->_scores;
	}

}