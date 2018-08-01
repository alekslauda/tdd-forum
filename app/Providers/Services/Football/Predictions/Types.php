<?php


namespace App\Providers\Services\Football\Predictions;


class Types
{
    const HOME_WIN = 'home.win';
    const DRAW = 'draw';
    const AWAY_WIN = 'away.win';
    const HOME_WIN_OR_DRAW = 'home.win.or.draw';
    const AWAY_WIN_OR_DRAW = 'away.win.or.draw';

    const BOTH_TEAMS_CAN_SCORE = 'both.teams.can.score';

    const OVER_1_5 = '1.5';
    const OVER_2_5 = '2.5';
    const OVER_3_5 = '3.5';
    const OVER_4_5 = '4.5';
}
