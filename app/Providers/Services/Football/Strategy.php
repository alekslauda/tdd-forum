<?php

namespace App\Providers\Services\Football;

use App\Providers\Services\Football\Strategies\StrategyChain;
use App\Providers\Services\Football\Strategies\Goals1and5;
use App\Providers\Services\Football\Strategies\Goals2and5;
use App\Providers\Services\Football\Strategies\Draw;
use App\Providers\Services\Football\Strategies\AwayWin;
use App\Providers\Services\Football\Strategies\HomeWin;
use App\Providers\Services\Football\Strategies\BothTeamsToScore;
use App\Providers\Services\Football\Strategies\CorrectScores;

class Strategy {


	protected $results = [];

	protected $predictions = [];

	protected $strategyChain;

	public function __construct($predictions, $results)
	{
		if ( !$predictions)
            throw new \Exception("Invalid data");

        $this->strategyChain = new strategyChain();
        $this->strategyChain->addStrategy(CorrectScores::$name, new CorrectScores());
        $this->strategyChain->addStrategy(HomeWin::$name, new HomeWin());
        $this->strategyChain->addStrategy(Draw::$name, new Draw());
        $this->strategyChain->addStrategy(AwayWin::$name, new AwayWin());
        $this->strategyChain->addStrategy(BothTeamsToScore::$name, new BothTeamsToScore());
		$this->predictions = $predictions;
		$this->results = $results;
		$this->buildStrategies();
	}

	protected function buildStrategies()
	{

		foreach ($this->predictions as $gameNum => $prediction) {
            $homeTeam = array_keys($prediction)[0];
            $awayTeam = array_keys($prediction)[1];


            foreach($this->results as $res) {
            	$results = explode('-', $res);
	            $homeTeamRes = (int) $results[0];
	            $awayTeamRes = (int) $results[1];

	            $homeFixture = $prediction[$homeTeam][$homeTeamRes];
	            $awayFixture = $prediction[$awayTeam][$awayTeamRes];

				$this->strategyChain->runStrategy('correct.scores', $homeFixture, $awayFixture, ['res' => $res]);

	        	if ($homeTeamRes > $awayTeamRes) {
        			$this->strategyChain->runStrategy('home.win', $homeFixture, $awayFixture);
	            }

	            if ($awayTeamRes > $homeTeamRes) {
	            	$this->strategyChain->runStrategy('away.win', $homeFixture, $awayFixture);
	            }

	            if($homeTeamRes === $awayTeamRes) {
	            	$this->strategyChain->runStrategy('draw', $homeFixture, $awayFixture);
	            }

	            if (($homeTeamRes + $awayTeamRes) > 2.5) {

	            }

	            if (($homeTeamRes + $awayTeamRes) > 1.5) {

	            }

	            if ($homeTeamRes > 0 && $awayTeamRes > 0) {
	            }
            }

        }
	}

	public function findStrategyByKey($key)
	{
		return $this->strategyChain->findStrategyByKey($key);
	}

	public function getAll()
	{
		return $this->strategyChain->getAll();
	}

}