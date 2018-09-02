<?php


namespace App\Providers\Services\Football\Predictions\Goals;


use App\Providers\Services\Football\Predictions\BetTypesInterface;
use App\Providers\Services\Football\Predictions\PredictionInterface;

abstract class BaseAbstract implements PredictionInterface
{
    protected $prediction;

    public function __construct(PredictionInterface $prediction)
    {
        $this->prediction = $prediction;
    }

    abstract function opposite();

    function getId()
    {
        return $this->prediction->getId();
    }

    function getTitle()
    {
        return $this->prediction->getTitle();
    }

    function getProbability()
    {
        return $this->prediction->getProbability();
    }

    function getOdds()
    {
        return $this->prediction->getOdds();
    }

    function getPercentage()
    {
        return $this->prediction->getPercentage();
    }

    function getVincent()
    {
        return $this->prediction->getVincent();
    }
}
