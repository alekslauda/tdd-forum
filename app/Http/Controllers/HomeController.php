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

//        dd(CompetitionBuilder::build());

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

            if($error) {
                throw $error;
            }
        }

        return view('home', ['data' => $data]);
    }
}
