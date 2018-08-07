<?php


namespace App\Providers\Services\Football\Predictions\Goals;

class Opposite extends BaseAbstract
{
    public function getOdds()
    {
        return round(1/((100 - round((1/(1/$this->prediction->getProbability()))*100, 2)) / 100), 2);
    }

    public function getPercentage()
    {
        return (100 - round((1/(1/$this->prediction->getProbability()))*100, 2));
    }

    public function opposite()
    {
        return new Standard($this);
    }
}
