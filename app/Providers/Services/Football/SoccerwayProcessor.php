<?php


namespace App\Providers\Services\Football;
use Goutte\Client;

class SoccerwayProcessor
{
    const HOME_TEAM_GOALS_FOR = 13;
    const HOME_TEAM_GOALS_AGAINST = 14;

    const AWAY_TEAM_GOALS_FOR = 19;
    const AWAY_TEAM_GOALS_AGAINST = 20;

    const HOME_TEAM_MATCH_PLAYED = 9;
    const AWAY_TEAM_MATCH_PLAYED = 15;

    protected $soccerwayCompetitionUrl;

    protected $matches;

    protected $results;

    protected $data;

    protected $occurances;

    protected $client;

    public function __construct($soccerwayCompetitionUrl, $matches, $occurances = 5)
    {
        $this->soccerwayCompetitionUrl = $soccerwayCompetitionUrl;
        $this->matches = $matches;
        $this->client = new Client();
        $this->setResults($occurances);
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

    protected function buildData()
    {
        $crawler = $this->client->request('GET', $this->soccerwayCompetitionUrl);
//        $crawler = $client->request('GET', 'https://int.soccerway.com/national/brazil/serie-b/2017/regular-season/r39675/');
        $options = $crawler->filter('#season_id_selector')->children();
        $getPastYear = $options->eq($options->count() + 1 - $options->count())->attr('value');
        $item = explode('/', $getPastYear);
        $season_id = (int) str_replace('s', '', $item[count($item) - 2]);
        $queryParams = [
            'block_id' => 'page_competition_1_block_competition_tables_7',
            'callback_params' => '{}',
            'action' => 'changeTable',
            'params' => '{"type":"competition_wide_table"}',

        ];

        $crawler->filter('#page_competition_1_block_competition_playerstats_8-wrapper + script')->each(function($node) use(&$queryParams, $season_id){
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
        $queryParams = http_build_query($queryParams);

        $ch = curl_init();
        $timeout = 5;

        curl_setopt($ch, CURLOPT_URL, 'https://int.soccerway.com/a/block_competition_tables?' . $queryParams);
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
        $crawler->clear();
        $crawler->addHtmlContent($html);
        $table = $crawler->filter('#page_competition_1_block_competition_tables_7_block_competition_wide_table_1_table')->filter('tr')->each(function ($tr, $i) {
            return $tr->filter('td')->each(function ($td, $i) {
                return trim($td->text());
            });
        });

        if( !$table) {
            throw new \RuntimeException('No data found.');
        }

        unset($table[0], $table[1]);
        $this->data = $table;
    }

    protected function setResults() {
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

    protected function getTeamStats($team, $option)
    {
        $teamNames = array_column($this->data, 2);
        $pos = false;
        foreach($teamNames as $k => $name) {
            if(mb_substr(mb_strtolower($name), 0, 5) === mb_substr(mb_strtolower($team), 0, 5)) {
                $pos = $k;
            }
        }

        if( $pos === false ) {
            throw new TeamNotFound('Please provide correct data. Team: ' . $team . ' not found.');
        }

        return (int) $this->data[$pos][$option];
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
