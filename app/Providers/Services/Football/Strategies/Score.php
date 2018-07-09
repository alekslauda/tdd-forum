<?php

namespace App\Providers\Services\Football\Strategies;


class Score extends Base {

	protected $possibility = 0;

	protected $flag = false;

	protected $forResult;


	public function __construct($forResult, $fixture1, $fixture2)
	{
		$this->forResult = $forResult;
		$this->possibility = $this->calcPossibility($fixture1, $fixture2);
	}


	public function getCorrectScore()
	{
		return $this->forResult;
	}

	public function getFlag()
	{
		return $this->convert('percentage') > 10;
	}

	public function setPossibilityType($type)
	{
		$this->possibility = $this->convert($type);
	}
}