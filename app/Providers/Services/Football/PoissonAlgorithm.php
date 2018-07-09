<?php
namespace App\Providers\Services\Football;

class TeamNotFound extends \Exception {}


class PoissonAlgorithm {

    protected $data = [];

    protected $matches = [];

    protected $occurances;

    protected $results = [];

    protected $spreadsheet_url;

    public function __construct(
        $spreadsheet_url,
        $matches,
        $occurances = 5
    ) {
        $this->matches = $matches;
        $this->occurances = $occurances;
        $this->spreadsheet_url = $spreadsheet_url;
        $this->loadData();
    }

    protected function loadData()
    {
        if(!ini_set('default_socket_timeout', 15)) echo "<!-- unable to change socket timeout -->";

        $info = [];
        if (($handle = fopen($this->spreadsheet_url, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $info[] = $data;
            }
            fclose($handle);
        }
        else
            die("Problem reading csv");

        if( !$info) {
            throw new \RuntimeException('No data found.');
        }
        unset($info[0], $info[1]);
        $this->data = array_values($info);
        $this->calculatePossibleOutcomes();
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

    const HOME_TEAM_GOALS_FOR = 13;
    const HOME_TEAM_GOALS_AGAINST = 14;

    const AWAY_TEAM_GOALS_FOR = 19;
    const AWAY_TEAM_GOALS_AGAINST = 20;

    const HOME_TEAM_MATCH_PLAYED = 9;
    const AWAY_TEAM_MATCH_PLAYED = 15;

    protected function getTeamStats($team, $option)
    {
        $teamNames = array_column($this->data, 2);

        $pos = false;
        $searchTeam = mb_substr($team, 0, 5);
        
        foreach($teamNames as $k => $name) {
            $arrTeam = mb_substr(mb_strtolower($name), 0, 5);
            
            if ($arrTeam === $searchTeam) {
                $pos = $k;
            }
        }

        if( $pos === false ) {
            throw new TeamNotFound('Please provide correct data. Team: ' . $team . ' not found.');
        }

        return (int) $this->data[$pos][$option];
    }

    public function generatePredictions()
    {
        $predictions = [];
        foreach($this->matches as $gameNum => $matches) {

            $homeTeam = trim(mb_strtolower($matches[0]));
            $awayTeam = trim(mb_strtolower($matches[1]));

            $teamsHomeGoalsScored = (int) array_sum(array_column($this->data, self::HOME_TEAM_GOALS_FOR));
            $teamsAwayGoalsScored = (int) array_sum(array_column($this->data, self::AWAY_TEAM_GOALS_FOR));
            $teamsTotalGamesPlayed = (int) round(number_format((array_sum(array_column($this->data, 3)) / 2), 1));
            $seasonAverageHomeGoals = $teamsHomeGoalsScored / $teamsTotalGamesPlayed;
            $seasonAverageAwayGoalsConceded = $teamsAwayGoalsScored / $teamsTotalGamesPlayed;
            $AVG_HOME = $teamsHomeGoalsScored / $teamsTotalGamesPlayed;
            $AVG_AWAY = $teamsAwayGoalsScored / $teamsTotalGamesPlayed;


            $homeTeamTotalGoalsScored = $this->getTeamStats($homeTeam, self::HOME_TEAM_GOALS_FOR);
            $homeTeamTotalGamesPlayed = $this->getTeamStats($homeTeam, self::HOME_TEAM_MATCH_PLAYED);
            $homeTeamAttackStrength = (double) ($homeTeamTotalGoalsScored / $homeTeamTotalGamesPlayed / $seasonAverageHomeGoals);
            $homeTeamTotalGoalsConceded = $this->getTeamStats($homeTeam, self::HOME_TEAM_GOALS_AGAINST);;
            $homeTeamDefenceStregnth = (double) ($homeTeamTotalGoalsConceded / $homeTeamTotalGamesPlayed / $seasonAverageAwayGoalsConceded);

            $awayTeamTotalGoalsConceded = $this->getTeamStats($awayTeam, self::AWAY_TEAM_GOALS_AGAINST);
            $awayTeamTotalGamesPlayed = $this->getTeamStats($awayTeam, self::AWAY_TEAM_MATCH_PLAYED);
            $awayTeamDefenceStrength = (double) ($awayTeamTotalGoalsConceded / $awayTeamTotalGamesPlayed / $seasonAverageHomeGoals);
            $awayTeamTotalGoalsScored = $this->getTeamStats($awayTeam, self::AWAY_TEAM_GOALS_FOR);
            $awayTeamAttackStregnth = ($awayTeamTotalGoalsScored / $awayTeamTotalGamesPlayed / $seasonAverageAwayGoalsConceded);


            $homeTeamGoalsProbability = (double) number_format($homeTeamAttackStrength * $awayTeamDefenceStrength * $AVG_HOME, 3);
            $awayTeamGoalsProbability = (double) number_format($awayTeamAttackStregnth * $homeTeamDefenceStregnth * $AVG_AWAY, 3);
            foreach (range(0,$this->occurances) as $occ => $v) {
                $predictions[($gameNum + 1)][$homeTeam][$occ] =  number_format(($this->poisson($homeTeamGoalsProbability, $occ)*100),2);
                $predictions[($gameNum + 1)][$awayTeam][$occ] =  number_format(($this->poisson($awayTeamGoalsProbability, $occ)*100),2);
            }
        }

        $strategy = new Strategy($predictions, $this->results);
        //todo
        dd($strategy->findStrategyByKey('correct.scores')->getResult(), $strategy->findStrategyByKey('correct.scores')->getResult()['0-0']);
        // return $this->calculateOdds($predictions);
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
            return [];


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

        foreach($this->results as $res) {
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