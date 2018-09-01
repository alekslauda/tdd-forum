<?php

namespace App\Http\Controllers;

use App\Providers\Services\Football\CompetitionBuilder;
use App\Providers\Services\Football\NoPredictionsWrongFileData;
use App\Providers\Services\Football\PoissonAlgorithmOddsConverter;
use App\Providers\Services\Football\Predictions\Factories\GoalsFactory;
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
                'competitions' => 'required'
            ]);

            try {
                
                $competitionUrl = $request->input('competitions');
                $soccerwayProcessor = new SoccerwayProcessor($competitionUrl);
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
            } catch (\Exception $e) {
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
