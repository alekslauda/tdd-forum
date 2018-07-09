<?php

namespace App\Http\Controllers;

use App\Providers\Services\Football\NoPredictionsWrongFileData;
use App\Providers\Services\Football\PoissonAlgorithm;
use App\Providers\Services\Football\TeamNotFound;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = [];
        $error = null;
        if ($request->isMethod('post')) {
            $this->validate($request, ['match.1' => 'required', 'sheet_url' => 'required', 'occurances' => 'required'], ['match.1.required' => 'Enter match game']);
            $matches = $request->input('match');
            $sheetUrl = $request->input('sheet_url');
            $occurances = $request->input('occurances');
            $games = [];
            foreach($matches as $match) {
                $game = explode('-', $match);
                if (count($game) == 2) {
                    $games[] = $game;
                }
            }

            try {
                $poisson = new PoissonAlgorithm($sheetUrl, $games, $occurances);
                $data = $poisson->generatePredictions();
            } catch (TeamNotFound $tex) {
                $error = ValidationException::withMessages([
                    'team_not_found' => [$tex->getMessage()],
                ]);
            } catch (NoPredictionsWrongFileData $ex) {
                $error = ValidationException::withMessages([
                    'team_not_found' => [$ex->getMessage()],
                ]);
            }

            if($error) {
                throw $error;
            }
        }

        return view('home', ['data' => $data]);
    }
}