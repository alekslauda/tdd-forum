<?php
namespace App\Providers\Services\Football;


use App\Providers\Services\Football\Predictions\AwayWin;
use App\Providers\Services\Football\Predictions\AwayWinOrDraw;
use App\Providers\Services\Football\Predictions\Draw;
use App\Providers\Services\Football\Predictions\Factories\GoalsFactory;
use App\Providers\Services\Football\Predictions\Factories\SignFactory;
use App\Providers\Services\Football\Predictions\Goals\Vincent2and5Strategy;
use App\Providers\Services\Football\Predictions\HomeWin;
use App\Providers\Services\Football\Predictions\HomeWinOrDraw;
use App\Providers\Services\Football\Predictions\Over;
use App\Providers\Services\Football\Predictions\Prediction;
use App\Providers\Services\Football\Predictions\Goals\Standard;
use App\Providers\Services\Football\Predictions\Types;

class TeamNotFound extends \Exception {}

class NoPredictionsWrongFileData extends \Exception {}


class PoissonAlgorithmOddsConverter {

    protected $soccerwayProcessor;

    const BET_AMOUNT = 10;

    public function __construct($soccerwayProcessor) {
        $this->soccerwayProcessor = $soccerwayProcessor;
    }

    protected function factorial($number)
    {
        if ($number < 2) {
            return 1;
        } else {
            return ($number * $this->factorial($number-1));
        }
    }

    protected function poisson($chance, $occurrence)
    {
        $e = exp(1);

        $a = pow($e, (-1 * $chance));
        $b = pow($chance,$occurrence);
        $c = $this->factorial($occurrence);

        return $a * $b / $c;
    }

    public function generatePredictions()
    {
        $predictions = [];
        $gameNum = 0;
        foreach($this->soccerwayProcessor->getMatches() as $t => $matches) {

            $gameNum++;

            $homeTeam = trim(($matches[0]));
            $awayTeam = trim(($matches[1]));

            try {
                $homeTeamTotalGoalsScored = $this->soccerwayProcessor->getTeamStats($homeTeam, SoccerwayProcessor::HOME_TEAM_GOALS_FOR);
                $homeTeamTotalGamesPlayed = $this->soccerwayProcessor->getTeamStats($homeTeam, SoccerwayProcessor::HOME_TEAM_MATCH_PLAYED);
                $homeTeamTotalGoalsConceded = $this->soccerwayProcessor->getTeamStats($homeTeam, SoccerwayProcessor::HOME_TEAM_GOALS_AGAINST);;
                $awayTeamTotalGoalsConceded = $this->soccerwayProcessor->getTeamStats($awayTeam, SoccerwayProcessor::AWAY_TEAM_GOALS_AGAINST);
                $awayTeamTotalGamesPlayed = $this->soccerwayProcessor->getTeamStats($awayTeam, SoccerwayProcessor::AWAY_TEAM_MATCH_PLAYED);
                $awayTeamTotalGoalsScored = $this->soccerwayProcessor->getTeamStats($awayTeam, SoccerwayProcessor::AWAY_TEAM_GOALS_FOR);

                $teamsHomeGoalsScored = $this->soccerwayProcessor->getTeamGoalsScored(SoccerwayProcessor::HOME_TEAM_GOALS_FOR);
                $teamsAwayGoalsScored = $this->soccerwayProcessor->getTeamGoalsScored(SoccerwayProcessor::AWAY_TEAM_GOALS_FOR);
            } catch (\Exception $ex) {
                echo '<pre>' . print_r($ex->getMessage(), true) . '</pre>';
                continue;
            }

            $teamsTotalGamesPlayed = $this->soccerwayProcessor->getTeamsTotalGamesPlayed();

            $seasonAverageHomeGoals = $teamsHomeGoalsScored / $teamsTotalGamesPlayed;
            $seasonAverageAwayGoalsConceded = $teamsAwayGoalsScored / $teamsTotalGamesPlayed;

            $AVG_HOME = $teamsHomeGoalsScored / $teamsTotalGamesPlayed;
            $AVG_AWAY = $teamsAwayGoalsScored / $teamsTotalGamesPlayed;

            $homeTeamAttackStrength = (double) ($homeTeamTotalGoalsScored / $homeTeamTotalGamesPlayed / $seasonAverageHomeGoals);
            $homeTeamDefenceStregnth = (double) ($homeTeamTotalGoalsConceded / $homeTeamTotalGamesPlayed / $seasonAverageAwayGoalsConceded);

            $awayTeamDefenceStrength = (double) ($awayTeamTotalGoalsConceded / $awayTeamTotalGamesPlayed / $seasonAverageHomeGoals);
            $awayTeamAttackStregnth = ($awayTeamTotalGoalsScored / $awayTeamTotalGamesPlayed / $seasonAverageAwayGoalsConceded);

            $homeTeamGoalsProbability = (double) number_format($homeTeamAttackStrength * $awayTeamDefenceStrength * $AVG_HOME, 3);
            $awayTeamGoalsProbability = (double) number_format($awayTeamAttackStregnth * $homeTeamDefenceStregnth * $AVG_AWAY, 3);

            foreach (range(0,SoccerwayProcessor::GOAL_OCCURANCES) as $occ => $v) {
                $predictions[($gameNum + 1)][$homeTeam][$occ] =  number_format(($this->poisson($homeTeamGoalsProbability, $occ)*100),2);
                $predictions[($gameNum + 1)][$awayTeam][$occ] =  number_format(($this->poisson($awayTeamGoalsProbability, $occ)*100),2);
            }
        }

        return $this->calculateOdds($predictions);
    }

    protected function calculateOdds($predictions)
    {
        if ( !$predictions)
            throw new NoPredictionsWrongFileData('No data. No predictions');


        $matchesResults = [];

        foreach ($predictions as $gameNum => $prediction) {
            $homeTeam = array_keys($prediction)[0];
            $awayTeam = array_keys($prediction)[1];
            $match = $homeTeam . ' - ' . $awayTeam;
            $results = $this->prediction($prediction, $homeTeam, $awayTeam);

            $matchesResults[$match] = [
                'beatTheBookie' => $results,
                'poisson' => $prediction,
            ];

        }
        return $matchesResults;
    }

    protected function prediction($prediction, $homeTeam, $awayTeam)
    {
        $correctScore = [];
        $homeWinPrediction = 0;
        $awayWinPrediction = 0;
        $drawPrediction = 0;
        $over0and5Prediction = 0;
        $over1and5Prediction = 0;
        $over2and5Prediction = 0;
        $over3and5Prediction = 0;
        $over4and5Prediction = 0;
        $bothTeamToScorePrediction = 0;

        foreach($this->soccerwayProcessor->getResults() as $res) {

            $results = explode('-', $res);
            $homeTeamRes = (int) $results[0];
            $awayTeamRes = (int) $results[1];

            $correctScore[$res] = $this->correctScorePredictor($prediction[$homeTeam][$homeTeamRes], $prediction[$awayTeam][$awayTeamRes]);

            if ($homeTeamRes > $awayTeamRes) {
                $converts = $this->convert($prediction[$homeTeam][$homeTeamRes], $prediction[$awayTeam][$awayTeamRes]);
                $homeWinPrediction += $converts[0] * $converts[1];
            }

            if ($awayTeamRes > $homeTeamRes) {
                $converts = $this->convert($prediction[$homeTeam][$homeTeamRes], $prediction[$awayTeam][$awayTeamRes]);
                $awayWinPrediction += $converts[0] * $converts[1];
            }

            if($homeTeamRes === $awayTeamRes) {
                $converts = $this->convert($prediction[$homeTeam][$homeTeamRes], $prediction[$awayTeam][$awayTeamRes]);
                $drawPrediction += $converts[0] * $converts[1];
            }

            if (($homeTeamRes + $awayTeamRes) > 4.5) {
                $converts = $this->convert($prediction[$homeTeam][$homeTeamRes], $prediction[$awayTeam][$awayTeamRes]);
                $over4and5Prediction += $converts[0] * $converts[1];
            }

            if (($homeTeamRes + $awayTeamRes) > 3.5) {
                $converts = $this->convert($prediction[$homeTeam][$homeTeamRes], $prediction[$awayTeam][$awayTeamRes]);
                $over3and5Prediction += $converts[0] * $converts[1];
            }

            if (($homeTeamRes + $awayTeamRes) > 2.5) {
                $converts = $this->convert($prediction[$homeTeam][$homeTeamRes], $prediction[$awayTeam][$awayTeamRes]);
                $over2and5Prediction += $converts[0] * $converts[1];
            }

            if (($homeTeamRes + $awayTeamRes) > 1.5) {
                $converts = $this->convert($prediction[$homeTeam][$homeTeamRes], $prediction[$awayTeam][$awayTeamRes]);
                $over1and5Prediction += $converts[0] * $converts[1];
            }

            if (($homeTeamRes + $awayTeamRes) > 0.5) {
                $converts = $this->convert($prediction[$homeTeam][$homeTeamRes], $prediction[$awayTeam][$awayTeamRes]);
                $over0and5Prediction += $converts[0] * $converts[1];
            }

            if ($homeTeamRes > 0 && $awayTeamRes > 0) {
                $converts = $this->convert($prediction[$homeTeam][$homeTeamRes], $prediction[$awayTeam][$awayTeamRes]);
                $bothTeamToScorePrediction += $converts[0] * $converts[1];
            }


        }

        SignFactory::addPrediction(new Prediction($homeWinPrediction, 'Home Win', Types::HOME_WIN));
        SignFactory::addPrediction(new Prediction($drawPrediction, 'Draw', Types::DRAW));
        SignFactory::addPrediction(new Prediction($awayWinPrediction, 'Away Win', Types::AWAY_WIN));
        SignFactory::addPrediction(new Prediction($homeWinPrediction + $drawPrediction, 'Home Win Or Draw', Types::HOME_WIN_OR_DRAW));
        SignFactory::addPrediction(new Prediction($awayWinPrediction + $drawPrediction, 'Away Win Or Draw', Types::AWAY_WIN_OR_DRAW));

        GoalsFactory::addPrediction(new Standard(new Prediction($over0and5Prediction, 'Over/Under 0.5', Types::OVER_0_5)));
        GoalsFactory::addPrediction(new Standard(new Prediction($over1and5Prediction, 'Over/Under 1.5', Types::OVER_1_5)));
        GoalsFactory::addPrediction(new Standard(new Prediction($over2and5Prediction, 'Over/Under 2.5', Types::OVER_2_5)));
        GoalsFactory::addPrediction(new Standard(new Prediction($over3and5Prediction, 'Over/Under 3.5', Types::OVER_3_5)));
        GoalsFactory::addPrediction(new Standard(new Prediction($over4and5Prediction, 'Over/Under 4.5', Types::OVER_4_5)));
        GoalsFactory::addPrediction(new Standard(new Prediction($bothTeamToScorePrediction, 'Both Teams Can Score', Types::BOTH_TEAMS_CAN_SCORE)));

        $vincentStrategyPossibility = $this->calculateVincentGoalStrategy([$homeTeam, $awayTeam]);

        $vincentPrediction = null;
        if($vincentStrategyPossibility != 0) {
            $vincentPrediction = new Vincent2and5Strategy($vincentStrategyPossibility, '', Types::VINCENT_OVER_UNDER_2_5);
        }

        return [
            'Sign' => SignFactory::getAll(),
            'Goals' => GoalsFactory::getAll(),
            'Vincent' => $vincentPrediction ? $vincentPrediction : null
        ];
    }

    protected function calculateVincentGoalStrategy(array $teams) {

        $results = $this->soccerwayProcessor->getTeamsLastMatches($teams);

        if( !$results['homeTeam'] || !$results['awayTeam']) {
            return 0;
        }

        $sum = 0;

        krsort($results['homeTeam']);
        krsort($results['awayTeam']);

        foreach($results as $team => $item) {
            // 4 is the key for the result
            $matches = array_column($item, 4);
            $lastMatches = array_slice($matches, -5);

            foreach ($lastMatches as $result) {
                $res = explode('-', $result);

                if (count($res) !== 2) {
                    continue;
                }

                $homeRes = (int) trim($res[0]);
                $awayRes = (int) trim($res[1]);

                if ($homeRes + $awayRes > 2.5) {
                    $sum += +0.5;
                } else {
                    $sum += -0.5;
                }

                if ($homeRes + $awayRes > 2.5 && $homeRes > 0 && $awayRes > 0) {
                    $sum += +0.75;
                }

                if ($homeRes == 0 || $awayRes == 0) {
                    $sum += -0.75;
                }
            }
        }

        return $sum;
    }


    protected function correctScorePredictor($fixture1, $fixture2)
    {
        $fixtures = $this->convert($fixture1, $fixture2);
        $fixtureCalc = $fixtures[0] * $fixtures[1];
        if($fixtureCalc == 0) {
            return;
        }
        $probability = round((1/(1/$fixtureCalc))*100, 2);
        return [
            'odds' => round((1 / ($fixtureCalc)), 2),
            'percentage' => $probability,
            'flagged' => $probability > 10,
        ];
    }

    protected function convert($f1, $f2)
    {
        return [$f1/100, $f2/100];
    }
}
