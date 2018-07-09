<?php

namespace App\Providers\Services\Football\Strategies;



class StrategyChain
{
  private $_strategies = array();
 
  public function addStrategy( $key, $strategy )
  {
    $this->_strategies[$key]= $strategy;
  }
 
  public function runStrategy( $name, $f1, $f2, $args = [] )
  {
    foreach( $this->_strategies as $strategy )
    {
      if ( $strategy->onStrategyCalled( $name, $f1, $f2, $args ) )
        return;
    }
  }

  public function findStrategyByKey($key)
  {
  	return $this->_strategies[$key];
  }

  public function getAll()
  {
  	return $this->_strategies;
  }
}