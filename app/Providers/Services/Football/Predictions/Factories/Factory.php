<?php


namespace App\Providers\Services\Football\Predictions\Factories;

use App\Providers\Services\Football\Predictions\PredictionInterface;


/**
 * Interface of Object Pool
 */
abstract class Factory extends AbstractFactory
{
    /**
     * It returns an instance of the class from which it was called
     * @return static
     */
    final public static function getInstance()
    {
        return parent::getInstance();
    }

    /**
     * Removes an instance of class
     * @return void
     */
    final public static function removeInstance()
    {
        parent::removeInstance();
    }

    /**
     * Adds product to the pool
     * @param PredictionInterface $prediction
     * @return void
     */
    public static function addPrediction(PredictionInterface $prediction)
    {
        self::getInstance()->predictions[$prediction->getId()] = $prediction;
    }

    /**
     * Returns product from the pool
     * @param int|string $id - product ID
     * @return PredictionInterface $prediction
     */
    public static function getPrediction($id)
    {
        return isset(self::getInstance()->predictions[$id]) ? self::getInstance()->predictions[$id] : null;
    }

    public static function getAll()
    {
        return self::getInstance()->predictions;
    }

}
