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
use Carbon\Carbon;
use Symfony\Component\DomCrawler\Crawler;

class TeamNotFound extends \Exception
{
}

class NoPredictionsWrongFileData extends \Exception
{
}


class PoissonAlgorithmOddsConverter
{
    /**
     * @var \App\Providers\Services\Football\SoccerwayProcessor
     */
    protected $soccerwayProcessor;

    const BET_AMOUNT = 10;

    public function __construct($soccerwayProcessor)
    {
        $this->soccerwayProcessor = $soccerwayProcessor;
    }

    protected function factorial($number)
    {
        if ($number < 2) {
            return 1;
        } else {
            return ($number * $this->factorial($number - 1));
        }
    }

    protected function poisson($chance, $occurrence)
    {
        $e = exp(1);

        $a = pow($e, (-1 * $chance));
        $b = pow($chance, $occurrence);
        $c = $this->factorial($occurrence);

        return $a * $b / $c;
    }

    public function generatePredictions()
    {
        $predictions = [];
        $gameNum = 0;
        foreach ($this->soccerwayProcessor->getMatches() as $t => $matches) {

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

            $homeTeamAttackStrength = (double)($homeTeamTotalGoalsScored / $homeTeamTotalGamesPlayed / $seasonAverageHomeGoals);
            $homeTeamDefenceStregnth = (double)($homeTeamTotalGoalsConceded / $homeTeamTotalGamesPlayed / $seasonAverageAwayGoalsConceded);

            $awayTeamDefenceStrength = (double)($awayTeamTotalGoalsConceded / $awayTeamTotalGamesPlayed / $seasonAverageHomeGoals);
            $awayTeamAttackStregnth = ($awayTeamTotalGoalsScored / $awayTeamTotalGamesPlayed / $seasonAverageAwayGoalsConceded);

            $homeTeamGoalsProbability = (double)number_format($homeTeamAttackStrength * $awayTeamDefenceStrength * $AVG_HOME, 3);
            $awayTeamGoalsProbability = (double)number_format($awayTeamAttackStregnth * $homeTeamDefenceStregnth * $AVG_AWAY, 3);

            foreach (range(0, SoccerwayProcessor::GOAL_OCCURANCES) as $occ => $v) {
                $predictions[($gameNum + 1)][$homeTeam][$occ] = number_format(($this->poisson($homeTeamGoalsProbability, $occ) * 100), 2);
                $predictions[($gameNum + 1)][$awayTeam][$occ] = number_format(($this->poisson($awayTeamGoalsProbability, $occ) * 100), 2);
            }
        }

        return $this->calculateOdds($predictions);
    }

    protected function calculateOdds($predictions)
    {
        if (!$predictions)
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

        foreach ($this->soccerwayProcessor->getResults() as $res) {

            $results = explode('-', $res);
            $homeTeamRes = (int)$results[0];
            $awayTeamRes = (int)$results[1];

            $correctScore[$res] = $this->correctScorePredictor($prediction[$homeTeam][$homeTeamRes], $prediction[$awayTeam][$awayTeamRes]);

            if ($homeTeamRes > $awayTeamRes) {
                $converts = $this->convert($prediction[$homeTeam][$homeTeamRes], $prediction[$awayTeam][$awayTeamRes]);
                $homeWinPrediction += $converts[0] * $converts[1];
            }

            if ($awayTeamRes > $homeTeamRes) {
                $converts = $this->convert($prediction[$homeTeam][$homeTeamRes], $prediction[$awayTeam][$awayTeamRes]);
                $awayWinPrediction += $converts[0] * $converts[1];
            }

            if ($homeTeamRes === $awayTeamRes) {
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

        $vincent = null;
        if ($vincentStrategyPossibility != 0) {
            $vincent = new Vincent2and5Strategy($vincentStrategyPossibility, '', Types::VINCENT_OVER_UNDER_2_5);
        }

        return [
            'Sign' => SignFactory::getAll(),
            'Goals' => GoalsFactory::getAll(),
            'Vincent' => $vincent ? $vincent : null,
        ];
    }

    protected function calculateVincentGoalStrategyH2H(array $teams)
    {
        $sum = 0;

        $teams = $this->soccerwayProcessor->getH2HMatches($teams);

        if (!$teams) {
            return 0;
        }

        $results = array_column($teams, 4);
        foreach ($results as $result) {
            $res = explode('-', $result);

            if (count($res) !== 2) {
                continue;
            }

            $homeRes = (int)trim($res[0]);
            $awayRes = (int)trim($res[1]);

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
        return $sum;
    }

    protected function calculateVincentGoalStrategy(array $teams)
    {

        $results = $this->soccerwayProcessor->getTeamsLastMatches($teams);

        $homeUrl = 'https://int.soccerway.com/a/block_match_team_matches?' . http_build_query([
                'block_id' => 'page_match_1_block_match_team_matches_14',
                'callback_params' => '{"page":"0","block_service_id":"match_summary_block_matchteammatches","team_id":" ' . $results['teamIds'][0] . ' ","competition_id":"0","filter":"home","new_design":""}',
                'action' => 'filterMatches',
                'params' => '{"filter":"home"}'
            ]);

        $awayUrl = 'https://int.soccerway.com/a/block_match_team_matches?' . http_build_query([
                'block_id' => 'page_match_1_block_match_team_matches_14',
                'callback_params' => '{"page":"0","block_service_id":"match_summary_block_matchteammatches","team_id":" ' . $results['teamIds'][1] . ' ","competition_id":"0","filter":"away","new_design":""}',
                'action' => 'filterMatches',
                'params' => '{"filter":"away"}'
            ]);

        $_h = curl_init();
        curl_setopt($_h, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($_h, CURLOPT_HTTPGET, 1);
        curl_setopt($_h, CURLOPT_URL, $homeUrl);
        curl_setopt($_h, CURLOPT_DNS_CACHE_TIMEOUT, 2);

        $data = curl_exec($_h);
        curl_close($_h);

        $soccerwayDataLastHomeMatches = json_decode($data, true);
        $homeHtml = $soccerwayDataLastHomeMatches['commands'][0]['parameters']['content'];

        $crawler = new Crawler();

        $crawler->addHtmlContent($homeHtml);
        $table1 = $crawler->filter('.matches')->filter('tr')->each(function ($tr, $i) {
            return $tr->filter('td')->each(function ($td, $i) {
                return trim($td->text());
            });
        });

        $_a = curl_init();
        curl_setopt($_a, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($_a, CURLOPT_HTTPGET, 1);
        curl_setopt($_a, CURLOPT_URL, $awayUrl);
        curl_setopt($_a, CURLOPT_DNS_CACHE_TIMEOUT, 2);

        $data = curl_exec($_a);
        curl_close($_a);

        $soccerwayDataLastAwayMatches = json_decode($data, true);
        $awayHtml = $soccerwayDataLastAwayMatches['commands'][0]['parameters']['content'];

        $crawler->clear();
        $crawler->addHtmlContent($awayHtml);

        $table2 = $crawler->filter('.matches')->filter('tr')->each(function ($tr, $i) {
            return $tr->filter('td')->each(function ($td, $i) {
                return trim($td->text());
            });
        });

        array_shift($table1);
        array_shift($table2);
        krsort($table1);
        krsort($table2);

        $checks = $this->checkForGoalPrediction(['home' => $table1, 'away' => $table2]);

        if (!$results['homeTeam'] || !$results['awayTeam']) {
            return 0;
        }

        krsort($results['homeTeam']);
        krsort($results['awayTeam']);

        $sum = 0;

        foreach ($results as $team => $item) {
            // 4 is the key for the result
            $matches = array_column($item, 4);
            $lastMatches = array_slice($matches, -5);

            foreach ($lastMatches as $result) {
                $res = explode('-', $result);

                if (count($res) !== 2) {
                    continue;
                }

                $homeRes = (int)trim($res[0]);
                $awayRes = (int)trim($res[1]);

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

        $h2hsum = $this->calculateVincentGoalStrategyH2H($teams);

        if ($checks) {

            if ($checks['homeOver'] && $checks['awayOver'] && $sum > 0 && $h2hsum > 0) {
                return $sum;
            } elseif ($checks['homeUnder'] && $checks['awayUnder'] && $sum < 0 && $h2hsum < 0) {
                return $sum;
            }

        }

        return 0;
    }

    protected function checkForGoalPrediction($tableStats)
    {
        $validations = [];

        if (!$tableStats) {
            return $validations;
        }

        if (!$tableStats['home']) {
            return $validations;
        }

        if (!$tableStats['away']) {
            return $validations;
        }


        $haveZeroScore = false;

        foreach ($tableStats as $team => $stats) {

            $countMatches = 3;
            $score = 0;
            $validOverGames = 0;
            $awayValidOverGames = 0;
            $previousGameCheck = false;
            $previousGame = 1;

            $validUnderGames = 0;
            $awayValidUnderGames = 0;

            foreach ($stats as $st) {
                try {
                    $todayDate = Carbon::today(SoccerwayProcessor::TIMEZONE);
                    $gameDate = Carbon::createFromFormat('d/m/y', $st[1]);
                } catch (\Exception $ex) {
                    continue;
                }

                if ($gameDate->gt($todayDate)) {
                    continue;
                }

                if ($countMatches > 0) {
                    $res = explode('-', $st[4]);
                    $resSum = array_sum($res);

                    if ($resSum > 2.5) {
                        $validOverGames++;
                    } else {
                        $validUnderGames++;
                    }
                    $score += $resSum;

                    if ($team === 'home' && $res[0] == 0 || $res[1] == 0) {
                        $haveZeroScore = true;
                    }

                    //additional checks for the away team
                    if ($team === 'away') {
                        if ($res[1] > 0) {
                            $awayValidOverGames++;
                        }

                        if ($res[1] == 0) {
                            $awayValidUnderGames++;
                        }

                        if ($previousGame == 1) {
                            $previousGameCheck = array_sum(explode('-', $st[4])) >= 2;
                            $previousGame--;
                        }
                    }

                    $countMatches--;

                }

            }

            if ($team == 'home') {
                $validations['homeOver'] = $score > 7 && $validOverGames >= 2;
                $validations['homeUnder'] = $validUnderGames >= 2 && $haveZeroScore;
            } else {
                $validations['awayOver'] = $score > 7 && $validOverGames >= 2 && $previousGameCheck && $awayValidOverGames >= 2;
                $validations['awayUnder'] = $validUnderGames >= 2 && $awayValidUnderGames >= 1;
            }

        }

        return $validations;
    }

    protected function correctScorePredictor($fixture1, $fixture2)
    {
        $fixtures = $this->convert($fixture1, $fixture2);
        $fixtureCalc = $fixtures[0] * $fixtures[1];
        if ($fixtureCalc == 0) {
            return;
        }
        $probability = round((1 / (1 / $fixtureCalc)) * 100, 2);
        return [
            'odds' => round((1 / ($fixtureCalc)), 2),
            'percentage' => $probability,
            'flagged' => $probability > 10,
        ];
    }

    protected function convert($f1, $f2)
    {
        return [$f1 / 100, $f2 / 100];
    }
}
