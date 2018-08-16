<?php


namespace App\Providers\Services\Football\Predictions\Goals;

use App\Providers\Services\Football\Predictions\Prediction;

class Vincent2and5Strategy extends Prediction
{
    public function __construct($probability, $title, $id)
    {
        parent::__construct($probability, $title, $id);
        $title = 'Over';
        if ($this->probability < 0) {
            $title = 'Under';
        }
        $this->title .= $title . ' 2.5 Goals';
    }

    public function subTitle()
    {
        return 'The percentage to bet from your stake';
    }

    public function getPercentage()
    {
        return round((abs($this->probability)/10) * 100, 2);
    }

    public function getOdds()
    {
        return round(1/($this->getPercentage()/100), 2);
    }

}
