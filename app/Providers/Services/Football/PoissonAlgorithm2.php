<?php
namespace App\Providers\Services\Football;


class TeamNotFound extends \Exception {}

class NoPredictionsWrongFileData extends \Exception {}


class PoissonAlgorithm2 {

    protected $soccerwayProcessor;

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
        foreach($this->soccerwayProcessor->getMatches() as $gameNum => $matches) {

            $homeTeam = trim(($matches[0]));
            $awayTeam = trim(($matches[1]));

            $teamsHomeGoalsScored = (int) array_sum(array_column($this->soccerwayProcessor->getData(), SoccerwayProcessor::HOME_TEAM_GOALS_FOR));
            $teamsAwayGoalsScored = (int) array_sum(array_column($this->soccerwayProcessor->getData(), SoccerwayProcessor::AWAY_TEAM_GOALS_FOR));
            $teamsTotalGamesPlayed = (int) round(number_format((array_sum(array_column($this->data, 3)) / 2), 1));
            $seasonAverageHomeGoals = $teamsHomeGoalsScored / $teamsTotalGamesPlayed;
            $seasonAverageAwayGoalsConceded = $teamsAwayGoalsScored / $teamsTotalGamesPlayed;
            $AVG_HOME = $teamsHomeGoalsScored / $teamsTotalGamesPlayed;
            $AVG_AWAY = $teamsAwayGoalsScored / $teamsTotalGamesPlayed;


            $homeTeamTotalGoalsScored = $this->soccerwayProcessor->getTeamStats($homeTeam, SoccerwayProcessor::HOME_TEAM_GOALS_FOR);
            $homeTeamTotalGamesPlayed = $this->soccerwayProcessor->getTeamStats($homeTeam, SoccerwayProcessor::HOME_TEAM_MATCH_PLAYED);
            $homeTeamAttackStrength = (double) ($homeTeamTotalGoalsScored / $homeTeamTotalGamesPlayed / $seasonAverageHomeGoals);
            $homeTeamTotalGoalsConceded = $this->soccerwayProcessor->getTeamStats($homeTeam, SoccerwayProcessor::HOME_TEAM_GOALS_AGAINST);;
            $homeTeamDefenceStregnth = (double) ($homeTeamTotalGoalsConceded / $homeTeamTotalGamesPlayed / $seasonAverageAwayGoalsConceded);

            $awayTeamTotalGoalsConceded = $this->soccerwayProcessor->getTeamStats($awayTeam, SoccerwayProcessor::AWAY_TEAM_GOALS_AGAINST);
            $awayTeamTotalGamesPlayed = $this->soccerwayProcessor->getTeamStats($awayTeam, SoccerwayProcessor::AWAY_TEAM_MATCH_PLAYED);
            $awayTeamDefenceStrength = (double) ($awayTeamTotalGoalsConceded / $awayTeamTotalGamesPlayed / $seasonAverageHomeGoals);
            $awayTeamTotalGoalsScored = $this->soccerwayProcessor->getTeamStats($awayTeam, SoccerwayProcessor::AWAY_TEAM_GOALS_FOR);
            $awayTeamAttackStregnth = ($awayTeamTotalGoalsScored / $awayTeamTotalGamesPlayed / $seasonAverageAwayGoalsConceded);


            $homeTeamGoalsProbability = (double) number_format($homeTeamAttackStrength * $awayTeamDefenceStrength * $AVG_HOME, 3);
            $awayTeamGoalsProbability = (double) number_format($awayTeamAttackStregnth * $homeTeamDefenceStregnth * $AVG_AWAY, 3);
            foreach (range(0,$this->occurances) as $occ => $v) {
                $predictions[($gameNum + 1)][$homeTeam][$occ] =  number_format(($this->poisson($homeTeamGoalsProbability, $occ)*100),2) . '%';
                $predictions[($gameNum + 1)][$awayTeam][$occ] =  number_format(($this->poisson($awayTeamGoalsProbability, $occ)*100),2) . '%';
            }
        }


        return $this->calculateOdds($predictions);
    }

    protected function calculatePossibleOutcomes() {
        $res = [];
        foreach(range(0, $this->occurances) as $k => $v) {

            for($i = 0; $i <= $v; $i++) {
                $res[] = $i . '-' . $v;
            }

            for($i = $this->occurances; $i >= $v; $i--) {
                $res[] = $i . '-' . $v;
            }
        }

        $this->results = array_unique($res);
    }

    protected function calculateOdds($predictions)
    {
        if ( !$predictions)
            throw new NoPredictionsWrongFileData('Please check your file and try again.');


        $matchesResults = [];

        foreach ($predictions as $gameNum => $prediction) {
            $homeTeam = array_keys($prediction)[0];
            $awayTeam = array_keys($prediction)[1];

            $matchesResults[$homeTeam . ' - ' . $awayTeam] = [
                'beatTheBookie' => $this->prediction($prediction, $homeTeam, $awayTeam),
                'poisson' => $prediction
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
        $over1and5Prediction = 0;
        $over2and5Prediction = 0;
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

            if (($homeTeamRes + $awayTeamRes) > 2.5) {
                $converts = $this->convert($prediction[$homeTeam][$homeTeamRes], $prediction[$awayTeam][$awayTeamRes]);
                $over2and5Prediction += $converts[0] * $converts[1];
            }

            if (($homeTeamRes + $awayTeamRes) > 1.5) {
                $converts = $this->convert($prediction[$homeTeam][$homeTeamRes], $prediction[$awayTeam][$awayTeamRes]);
                $over1and5Prediction += $converts[0] * $converts[1];
            }

            if ($homeTeamRes > 0 && $awayTeamRes > 0) {
                $converts = $this->convert($prediction[$homeTeam][$homeTeamRes], $prediction[$awayTeam][$awayTeamRes]);
                $bothTeamToScorePrediction += $converts[0] * $converts[1];
            }


        }

        $homeWin = round((1/$homeWinPrediction), 2) . '(' . round((1/(1/$homeWinPrediction))*100, 2) . '%' . ')';
        $draw = round((1/$drawPrediction), 2) . '(' . round((1/(1/$drawPrediction))*100, 2) . '%' . ')';
        $awayWin = round((1/$awayWinPrediction), 2) . '(' . round((1/(1/$awayWinPrediction))*100, 2) . '%' . ')';
        $overUnder1and5 = [
            'over 1.5' => round((1/$over1and5Prediction), 2) . '(' . round((1/(1/$over1and5Prediction))*100, 2) . '%' . ')',
            'under 1.5' => round(1/((100 - round((1/(1/$over1and5Prediction))*100, 2)) / 100), 2) . '(' . (100 - round((1/(1/$over1and5Prediction))*100, 2)) . '%' . ')',
        ];
        $overUnder2and5 = [
            'over 2.5' => round((1/$over2and5Prediction), 2) . '(' . round((1/(1/$over2and5Prediction))*100, 2) . '%' . ')',
            'under 2.5' => round(1/((100 - round((1/(1/$over2and5Prediction))*100, 2)) / 100), 2) . '(' . (100 - round((1/(1/$over2and5Prediction))*100, 2)) . '%' . ')',
        ];

        $bothTeamToScore = [
            'Yes' => round((1/$bothTeamToScorePrediction), 2) . '(' .round((1/(1/$bothTeamToScorePrediction))*100, 2) . '%' . ')',
            'No' => round(1/((100 - round((1/(1/$bothTeamToScorePrediction))*100, 2)) / 100), 2) . '(' . (100 - round((1/(1/$bothTeamToScorePrediction))*100, 2)) . '%' . ')'
        ];

        return [
            'Home Win' => $homeWin,
            'Draw' => $draw,
            'Away Win' => $awayWin,
            'Over/Under 1.5' => $overUnder1and5,
            'Over/Under 2.5' => $overUnder2and5,
            'Both Teams To Score' => $bothTeamToScore,
            'Correct Score' => $correctScore
        ];
    }

    protected function correctScorePredictor($fixture1, $fixture2)
    {
        $fixtures = $this->convert($fixture1, $fixture2);
        $fixtureCalc = $fixtures[0] * $fixtures[1];
        $probability = round((1/(1/$fixtureCalc))*100, 2);
        return [
            'stats' => round((1 / ($fixtureCalc)), 2). '(' . $probability . '%' . ')',
            'flagged' => $probability > 10,
        ];
    }

    protected function convert($f1, $f2)
    {
        $fix1 = (double) str_replace('%', '', $f1);
        $fix2 = (double) str_replace('%', '', $f2);
        return [$fix1/100, $fix2/100];
    }
}
