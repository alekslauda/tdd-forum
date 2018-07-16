<?php

namespace App\Http\Controllers;

use App\Providers\Services\Football\CompetitionBuilder;
use App\Providers\Services\Football\NoPredictionsWrongFileData;
use App\Providers\Services\Football\PoissonAlgorithmOddsConverter;
use App\Providers\Services\Football\SoccerwayProcessor;
use App\Providers\Services\Football\TeamNotFound;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $data = [];
        $error = null;

        if ($request->isMethod('post')) {
            $this->validate($request, [
                'match.1' => 'required',
                'competitions' => 'required'
            ], [
                'match.1.required' => 'Enter match game'
            ]);
            $matches = $request->input('match');
            $competitionUrl = $request->input('competitions');
            $games = [];
            foreach($matches as $match) {
                $game = explode('-', $match);
                if (count($game) == 2) {
                    $games[] = $game;
                }
            }

            try {
                $soccerwayProcessor = new SoccerwayProcessor($competitionUrl, $games);
                $poisson = new PoissonAlgorithmOddsConverter($soccerwayProcessor);
                $data = $poisson->generatePredictions();



            } catch (TeamNotFound $tex) {
                \Log::error('System error: '. $tex->getMessage() . ' | Trace: ' . $tex->getTraceAsString());
                $error = ValidationException::withMessages([
                    'team_not_found' => [$tex->getMessage()],
                ]);
            } catch (NoPredictionsWrongFileData $ex) {
                $error = ValidationException::withMessages([
                    'team_not_found' => [$ex->getMessage()],
                ]);
            } catch (\ErrorException $e) {
                \Log::error('System error: '. $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
                $error = ValidationException::withMessages([
                    'system_error' => ['Something went wrong. Please try again later'],
                ]);
            }

            $possibleValueBetting = [];

            foreach($data as $k =>$v) {

                $possibleValueBetting[$k]['Sign'] = [
                    'Home Win' => $poisson->findValueBet(
                        $v['beatTheBookie']['Home Win']['odds'],
                        $v['beatTheBookie']['Home Win']['percentage'],
                        $v['beatTheBookie']['Away Win']['percentage'] + $v['beatTheBookie']['Draw']['percentage']
                    ),
                    'Draw' => $poisson->findValueBet(
                        $v['beatTheBookie']['Draw']['odds'],
                        $v['beatTheBookie']['Draw']['percentage'],
                        $v['beatTheBookie']['Home Win']['percentage'] + $v['beatTheBookie']['Away Win']['percentage']
                    ),
                    'Away Win' =>$poisson->findValueBet(
                        $v['beatTheBookie']['Away Win']['odds'],
                        $v['beatTheBookie']['Away Win']['percentage'],
                        $v['beatTheBookie']['Home Win']['percentage'] + $v['beatTheBookie']['Draw']['percentage']
                    )
                ];

                $possibleValueBetting[$k]['Goals']['Over 1.5'] = $poisson->findValueBet(
                    $v['beatTheBookie']['Over/Under 1.5']['over 1.5']['odds'],
                    $v['beatTheBookie']['Over/Under 1.5']['over 1.5']['percentage'],
                    $v['beatTheBookie']['Over/Under 1.5']['under 1.5']['percentage']
                );

                $possibleValueBetting[$k]['Goals']['Over 2.5'] = $poisson->findValueBet(
                    $v['beatTheBookie']['Over/Under 2.5']['over 2.5']['odds'],
                    $v['beatTheBookie']['Over/Under 2.5']['over 2.5']['percentage'],
                    $v['beatTheBookie']['Over/Under 2.5']['under 2.5']['percentage']
                );

                $possibleValueBetting[$k]['Goals']['Under 1.5'] = $poisson->findValueBet(
                    $v['beatTheBookie']['Over/Under 1.5']['under 1.5']['odds'],
                    $v['beatTheBookie']['Over/Under 1.5']['under 1.5']['percentage'],
                    $v['beatTheBookie']['Over/Under 1.5']['over 1.5']['percentage']
                );

                $possibleValueBetting[$k]['Goals']['Under 2.5'] = $poisson->findValueBet(
                    $v['beatTheBookie']['Over/Under 2.5']['under 2.5']['odds'],
                    $v['beatTheBookie']['Over/Under 2.5']['under 2.5']['percentage'],
                    $v['beatTheBookie']['Over/Under 2.5']['over 2.5']['percentage']
                );

                $possibleValueBetting[$k]['Goals']['Over 1.5'] = $poisson->findValueBet(
                    $v['beatTheBookie']['Over/Under 1.5']['over 1.5']['odds'],
                    $v['beatTheBookie']['Over/Under 1.5']['over 1.5']['percentage'],
                    $v['beatTheBookie']['Over/Under 1.5']['under 1.5']['percentage']
                );

                $possibleValueBetting[$k]['Goals']['Over 2.5'] = $poisson->findValueBet(
                    $v['beatTheBookie']['Over/Under 2.5']['over 2.5']['odds'],
                    $v['beatTheBookie']['Over/Under 2.5']['over 2.5']['percentage'],
                    $v['beatTheBookie']['Over/Under 2.5']['under 2.5']['percentage']
                );

                $possibleValueBetting[$k]['Both Teams To Score']['yes'] = $poisson->findValueBet(
                    $v['beatTheBookie']['Both Teams To Score']['Yes']['odds'],
                    $v['beatTheBookie']['Both Teams To Score']['Yes']['percentage'],
                    $v['beatTheBookie']['Both Teams To Score']['No']['percentage']
                );

                $possibleValueBetting[$k]['Both Teams To Score']['no'] = $poisson->findValueBet(
                    $v['beatTheBookie']['Both Teams To Score']['No']['odds'],
                    $v['beatTheBookie']['Both Teams To Score']['No']['percentage'],
                    $v['beatTheBookie']['Both Teams To Score']['Yes']['percentage']
                );
            }

            if($error) {
                throw $error;
            }
        }

        return view('home', ['data' => $data, 'valueBets' => $possibleValueBetting]);
    }
}
