<?php


namespace App\Providers\Services\Football\Predictions;


interface PredictionInterface
{
    function getId();
    function getTitle();
    function getProbability();
    function getOdds();
    function getPercentage();
}
