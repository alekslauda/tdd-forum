<?php


namespace App\Providers\Services\Football;


use Goutte\Client;

class CompetitionBuilder
{
    public static function build()
    {
        $client = new Client();
        $crawler = $client->request('GET', \Config::get('app.SOCCERWAY_URL') . 'competitions/');

        $competitions = [];
        $crawler->filter('ul.areas')->each(function($node) use (&$competitions, $client){
            $node->filter('li')->each(function($li) use(&$competitions, $client){
                $competitions[trim($li->text())] = $li->filter('a')->attr('href');
            });
        });

        return $competitions;
    }

    public static function buildCompetitions($link)
    {
        $client = new Client();
        $client->request('GET', \Config::get('app.SOCCERWAY_URL') . $link)->filter('ul.left-tree')->each(function($node) use (&$children){
            $node->filter('li')->each(function($li) use(&$children){
                $children[$li->filter('a')->text()] = $li->filter('a')->attr('href');
            });
        });

        return $children;
    }

}
