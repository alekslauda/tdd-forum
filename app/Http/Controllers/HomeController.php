<?php

namespace App\Http\Controllers;

use App\Providers\Services\Football\PoissonAlgorithm;
use Illuminate\Http\Request;

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

        if ($request->isMethod('post')) {
            $this->validate($request, ['match' => 'required', 'sheet_url' => 'required', 'occurances' => 'required']);
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

            $poisson = new PoissonAlgorithm($sheetUrl, $games, $occurances);
            $data = $poisson->generatePredictions();
            if( !$data) {
                throw new \Exception('Try again');
            }
        }

        return view('home', ['data' => $data]);
    }
}
