<?php
namespace App\Providers\Services\Football;


class TeamNotFound extends \Exception {}

class NoPredictionsWrongFileData extends \Exception {}


class PoissonAlgorithmOddsConverter {

    protected $soccerwayProcessor;

    const BET_AMOUNT = 50;

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

    protected function findValueBet($winInOdds, $winChancePercentage, $lossChancePercentage)
    {
        $winChance = round($winChancePercentage/100, 2);
        $lossChance = round($lossChancePercentage/100, 2);
        $amountWonPerBet = (self::BET_AMOUNT * $winInOdds) - self::BET_AMOUNT;
        return round(($amountWonPerBet * $winChance) - (self::BET_AMOUNT * $lossChance), 2);
    }

    public function generatePredictions()
    {
        $predictions = [];
        foreach($this->soccerwayProcessor->getMatches() as $gameNum => $matches) {

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
                $predictions[($gameNum + 1)][$homeTeam][$occ] =  number_format(($this->poisson($homeTeamGoalsProbability, $occ)*100),2) . '%';
                $predictions[($gameNum + 1)][$awayTeam][$occ] =  number_format(($this->poisson($awayTeamGoalsProbability, $occ)*100),2) . '%';
            }
        }

        return $this->calculateOdds($predictions);
    }

    protected function calculateOdds($predictions)
    {
        if ( !$predictions)
            throw new NoPredictionsWrongFileData('No data. No predictions');


        $matchesResults = [];
        $possibleValueBetting = [];

        foreach ($predictions as $gameNum => $prediction) {
            $homeTeam = array_keys($prediction)[0];
            $awayTeam = array_keys($prediction)[1];
            $match = $homeTeam . ' - ' . $awayTeam;
            $results = $this->prediction($prediction, $homeTeam, $awayTeam);

            $possibleValueBetting = [
                'Sign' => [
                    'Home Win' => $this->findValueBet(
                        $results['Home Win']['odds'],
                        $results['Home Win']['percentage'],
                        $results['Away Win']['percentage'] + $results['Draw']['percentage']
                    ),
                    'Draw' => $this->findValueBet(
                        $results['Draw']['odds'],
                        $results['Draw']['percentage'],
                        $results['Home Win']['percentage'] + $results['Away Win']['percentage']
                    ),
                    'Away Win' =>$this->findValueBet(
                        $results['Away Win']['odds'],
                        $results['Away Win']['percentage'],
                        $results['Home Win']['percentage'] + $results['Draw']['percentage']
                    )
                ],
            ];
            $possibleValueBetting['Goals']['Over 1.5'] = $this->findValueBet(
                $results['Over/Under 1.5']['over 1.5']['odds'],
                $results['Over/Under 1.5']['over 1.5']['percentage'],
                $results['Over/Under 1.5']['under 1.5']['percentage']
            );

            $possibleValueBetting['Goals']['Over 2.5'] = $this->findValueBet(
                $results['Over/Under 2.5']['over 2.5']['odds'],
                $results['Over/Under 2.5']['over 2.5']['percentage'],
                $results['Over/Under 2.5']['under 2.5']['percentage']
            );

            $possibleValueBetting['Goals']['Under 1.5'] = $this->findValueBet(
                $results['Over/Under 1.5']['under 1.5']['odds'],
                $results['Over/Under 1.5']['under 1.5']['percentage'],
                $results['Over/Under 1.5']['over 1.5']['percentage']
            );

            $possibleValueBetting['Goals']['Under 2.5'] = $this->findValueBet(
                $results['Over/Under 2.5']['under 2.5']['odds'],
                $results['Over/Under 2.5']['under 2.5']['percentage'],
                $results['Over/Under 2.5']['over 2.5']['percentage']
            );

            $possibleValueBetting['Goals']['Over 1.5'] = $this->findValueBet(
                $results['Over/Under 1.5']['over 1.5']['odds'],
                $results['Over/Under 1.5']['over 1.5']['percentage'],
                $results['Over/Under 1.5']['under 1.5']['percentage']
            );

            $possibleValueBetting['Goals']['Over 2.5'] = $this->findValueBet(
                $results['Over/Under 2.5']['over 2.5']['odds'],
                $results['Over/Under 2.5']['over 2.5']['percentage'],
                $results['Over/Under 2.5']['under 2.5']['percentage']
            );

            $possibleValueBetting['Both Teams To Score']['Yes'] = $this->findValueBet(
                $results['Both Teams To Score']['Yes']['odds'],
                $results['Both Teams To Score']['Yes']['percentage'],
                $results['Both Teams To Score']['No']['percentage']
            );

            $possibleValueBetting['Both Teams To Score']['No'] = $this->findValueBet(
                $results['Both Teams To Score']['No']['odds'],
                $results['Both Teams To Score']['No']['percentage'],
                $results['Both Teams To Score']['Yes']['percentage']
            );

            $matchesResults[$match] = [
                'beatTheBookie' => $results,
                'poisson' => $prediction,
                'valueBets' => $possibleValueBetting
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

            if ($homeTeamRes > 0 && $awayTeamRes > 0) {
                $converts = $this->convert($prediction[$homeTeam][$homeTeamRes], $prediction[$awayTeam][$awayTeamRes]);
                $bothTeamToScorePrediction += $converts[0] * $converts[1];
            }


        }

        $homeWin = [
            'odds' => round((1/$homeWinPrediction), 2),
            'percentage' =>round((1/(1/$homeWinPrediction))*100, 2)
        ];

        $draw = [
            'odds' => round((1/$drawPrediction), 2),
            'percentage' =>round((1/(1/$drawPrediction))*100, 2)
        ];
        $awayWin = [
            'odds' => round((1/$awayWinPrediction), 2),
            'percentage' =>round((1/(1/$awayWinPrediction))*100, 2)
        ];
        $overUnder1and5 = [
            'over 1.5' => [
                'odds' => round((1/$over1and5Prediction), 2),
                'percentage' => round((1/(1/$over1and5Prediction))*100, 2)
            ],
            'under 1.5' => [
                'odds' => round(1/((100 - round((1/(1/$over1and5Prediction))*100, 2)) / 100), 2),
                'percentage' => (100 - round((1/(1/$over1and5Prediction))*100, 2)),
            ],
        ];
        $overUnder2and5 = [
            'over 2.5' => [
                'odds' => round((1/$over2and5Prediction), 2),
                'percentage' => round((1/(1/$over2and5Prediction))*100, 2)
            ],
            'under 2.5' => [
                'odds' => round(1/((100 - round((1/(1/$over2and5Prediction))*100, 2)) / 100), 2),
                'percentage' => (100 - round((1/(1/$over2and5Prediction))*100, 2)),
            ],
        ];
        $overUnder3and5 = [
            'over 3.5' => [
                'odds' => round((1/$over3and5Prediction), 2),
                'percentage' => round((1/(1/$over3and5Prediction))*100, 2)
            ],
            'under 3.5' => [
                'odds' => round(1/((100 - round((1/(1/$over3and5Prediction))*100, 2)) / 100), 2),
                'percentage' => (100 - round((1/(1/$over3and5Prediction))*100, 2)),
            ],
        ];
        $overUnder4and5 = [
            'over 4.5' => [
                'odds' => round((1/$over4and5Prediction), 2),
                'percentage' => round((1/(1/$over4and5Prediction))*100, 2)
            ],
            'under 4.5' => [
                'odds' => round(1/((100 - round((1/(1/$over4and5Prediction))*100, 2)) / 100), 2),
                'percentage' => (100 - round((1/(1/$over4and5Prediction))*100, 2)),
            ],
        ];

        $bothTeamToScore = [
            'Yes' => [
                'odds' => round((1/$bothTeamToScorePrediction), 2),
                'percentage' => round((1/(1/$bothTeamToScorePrediction))*100, 2)
            ],
            'No' => [
                'odds' => round(1/((100 - round((1/(1/$bothTeamToScorePrediction))*100, 2)) / 100), 2),
                'percentage' => (100 - round((1/(1/$bothTeamToScorePrediction))*100, 2))
            ]
        ];

        return [
            'Home Win' => $homeWin,
            'Draw' => $draw,
            'Away Win' => $awayWin,
            'Over/Under 1.5' => $overUnder1and5,
            'Over/Under 2.5' => $overUnder2and5,
            'Over/Under 3.5' => $overUnder3and5,
            'Over/Under 4.5' => $overUnder4and5,
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
