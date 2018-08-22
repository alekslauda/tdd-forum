<?php


namespace App\Providers\Services\Football\Predictions\Goals;


class Standard extends BaseAbstract
{
    public function opposite()
    {
        return new Opposite($this);
    }

    public function getOdds()
    {
        return $this->prediction->getOdds();
    }

    public function getPercentage()
    {
        return $this->prediction->getPercentage();
    }
}