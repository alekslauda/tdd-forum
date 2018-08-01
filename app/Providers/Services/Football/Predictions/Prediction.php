<?php


namespace App\Providers\Services\Football\Predictions;


class Prediction implements PredictionInterface
{
    protected $probability;
    protected $title;
    protected $id;

    public function __construct($probability, $title, $id)
    {
        $this->probability = $probability;
        $this->title = $title;
        $this->id= $id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getProbability()
    {
        return $this->probability;
    }

    function getOdds()
    {
        return round((1/$this->probability), 2);
    }

    function getPercentage()
    {
        return round((1/(1/$this->probability))*100, 2);
    }

}
