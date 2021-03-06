<?php


namespace App\Providers\Services\Football;
use Carbon\Carbon;
use Goutte\Client;

class SoccerwayProcessor
{
    const HOME_TEAM_GOALS_FOR = 13;
    const HOME_TEAM_GOALS_AGAINST = 14;

    const AWAY_TEAM_GOALS_FOR = 19;
    const AWAY_TEAM_GOALS_AGAINST = 20;

    const HOME_TEAM_MATCH_PLAYED = 9;
    const AWAY_TEAM_MATCH_PLAYED = 15;

    const GOAL_OCCURANCES = 5;

    const EXTRACT_TODAY_DATE = 1;
    const EXTRACT_TODAY_HOME_TEAM = 2;
    const EXTRACT_TODAY_AWAY_TEAM = 4;

    const TIMEZONE = 'Europe/Sofia';

    protected $soccerwayCompetitionUrl;

    protected $matches;

    protected $results;

    protected $data;

    protected $client;

    public function __construct($soccerwayCompetitionUrl)
    {
        $this->soccerwayCompetitionUrl = $soccerwayCompetitionUrl;
        $this->client = new Client();
        $this->setResults();
        $this->buildData();
    }

    public function getMatches()
    {
        return $this->matches;
    }

    public function getResults()
    {
        return $this->results;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getTeamGoalsScored($team = self::HOME_TEAM_GOALS_FOR)
    {
        return (int) array_sum(array_column($this->data, $team));
    }

    public function getTeamsTotalGamesPlayed()
    {
        return (int) round(number_format((array_sum(array_column($this->data, 3)) / 2), 1));
    }

    public function getTeamStats($team, $option)
    {
        $teamNames = array_column($this->data, 2);
        $pos = false;
        $originTeam = $team;
        foreach($teamNames as $k => $name) {

            $dots = strpos($name, '…');
            $removeDotsName = str_replace('…', '', $name);

            if( $dots !== false) {
                if(mb_substr(mb_strtolower($team), 0, 5) === mb_substr(mb_strtolower($removeDotsName), 0, 5)) {
                    $team = (mb_substr($team, 0, -(mb_strlen($team)-$dots+1)));
                }
            }
            if($removeDotsName === $team) {
                $pos = $k;
            }

        }
        if( $pos === false ) {
            throw new TeamNotFound('Please provide correct data. Team: ' . $originTeam . ' not found.');
        }

        return (int) $this->data[$pos][$option];
    }

    protected function buildData()
    {
        $crawler = $this->client->request('GET', \Config::get('app.SOCCERWAY_URL') . $this->soccerwayCompetitionUrl);

        $this->buildTodayMatches($crawler);

        $options = $crawler->filter('#season_id_selector')->children();
        $getPastYearLink = $options->eq($options->count() + 1 - $options->count())->attr('value');
        $item = explode('/', $getPastYearLink);
        $season_id = (int) str_replace('s', '', $item[count($item) - 2]);
        $queryParams = [
            'block_id' => 'page_competition_1_block_competition_tables_7',
            'callback_params' => '{}',
            'action' => 'changeTable',
            'params' => '{"type":"competition_wide_table"}',
        ];

        $crawler2 = $this->client->request('GET', \Config::get('app.SOCCERWAY_URL') . $getPastYearLink);
        $crawler2->filter('#page_competition_1_block_competition_playerstats_8-wrapper + script')->each(function($node) use(&$queryParams, $season_id){
            $params = $this->extractJSON($node->text());

            if( !$params) {
                throw new \Exception('Try again');
            }

            $parsed = json_decode($params[0]);
            $parsed->season_id = $season_id;
            $parsed->outgroup = "";
            $parsed->view = "";
            $parsed->new_design_callback = "";

            $queryParams['callback_params'] = json_encode($parsed);

        });

        $buildQueryParams = http_build_query($queryParams);

        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, \Config::get('app.SOCCERWAY_URL') . '/a/block_competition_tables?' . $buildQueryParams);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        $data = curl_exec($ch);
        curl_close($ch);

        $soccerwayData = json_decode($data, true);

        if( !$soccerwayData) {
            throw new \Exception('Invalid data');
        }

        $html = $soccerwayData['commands'][0]['parameters']['content'];
        $crawler2->clear();
        $crawler2->addHtmlContent($html);

        $table = $crawler2->filter('#page_competition_1_block_competition_tables_7_block_competition_wide_table_1_table')->filter('tr')->each(function ($tr, $i) {
            return $tr->filter('td')->each(function ($td, $i) {
                return trim($td->text());
            });
        });

        unset($table[0], $table[1]);
        $this->data = array_values($table);
    }

    protected function buildTodayMatches($crawler)
    {
        $todayDate = Carbon::today(static::TIMEZONE);

        $table = $crawler->filter('#page_competition_1_block_competition_matches_summary_5')->filter('tr')->each(function ($tr, $i) {
            return $tr->filter('td')->each(function ($td, $i) {
                return trim($td->text());
            });
        });

        array_shift($table);
        $this->matches = [];

        foreach($table as $games) {

            $gameDate = Carbon::createFromFormat('d/m/y' ,$games[self::EXTRACT_TODAY_DATE]);
            $parsedGameDate = Carbon::parse($gameDate->format('Y-m-d'), static::TIMEZONE);

            if ($todayDate->eq($parsedGameDate)) {
                $this->matches[] = [$games[self::EXTRACT_TODAY_HOME_TEAM], $games[self::EXTRACT_TODAY_AWAY_TEAM]];
            }
        }

    }

    protected function setResults() {
        $res = [];
        foreach(range(0, self::GOAL_OCCURANCES) as $k => $v) {

            for($i = 0; $i <= $v; $i++) {
                $res[] = $i . '-' . $v;
            }

            for($i = self::GOAL_OCCURANCES; $i >= $v; $i--) {
                $res[] = $i . '-' . $v;
            }
        }
        $this->results = array_unique($res);
    }

    private function extractJSON($string) {
        $pattern = '
        /
        \{              # { character
            (?:         # non-capturing group
                [^{}]   # anything that is not a { or }
                |       # OR
                (?R)    # recurses the entire pattern
            )*          # previous group zero or more times
        \}              # } character
        /x
        ';

        preg_match_all($pattern, $string, $matches);

        if ($matches == false) {
            return [];
        }

        $result = json_decode($matches[0][0]);
        if (!$result) {
            return $this->extractJSON(substr($matches[0][0], 1, -1));
        }

        return $matches[0];
    }
}
