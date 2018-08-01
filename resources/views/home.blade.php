@extends('layouts.app')

@section('content')
<div class="container">

    @if (count($errors))
        <ul class="list-unstyled alert alert-danger">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    @if($data)
    <div class="row">
        <a style="margin-bottom: 30px" href="/home" class="btn btn-info" role="button">Search again</a>

        <div class="alert alert-warning">
            <strong>Calculate Value Bet!</strong> Click on some percentage probability to calculate if there is any value.
        </div>

        <a id="back-to-down" href="#" class="btn btn-primary btn-lg back-to-down" role="button" title="Click to return on the bottom of the page" data-toggle="tooltip" data-placement="left"><span class="glyphicon glyphicon-chevron-down"></span></a>
    </div>

    <div class="row">
        <div class="panel panel-success">
            @foreach($data as $match => $v)
                <div class="panel-heading">{{ $match }}</div>
                <div class="panel-body">

                    <div class="panel panel-info">
                        <div class="panel-heading">Sign</div>
                        <div class="panel-body">
                            @foreach($v['beatTheBookie']['Sign'] as $prediction)
                                <div class="col-md-4">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">{{ $prediction->getTitle() }}</div>
                                        <div class="panel-body">
                                            <div class="progress">
                                                <div class="progress-bar " role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: {{ $prediction->getPercentage() }}%;">
                                                    {{ $prediction->getPercentage() }}%
                                                </div>
                                            </div>
                                            <span>Odds:  {{ $prediction->getOdds() }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="clearfix"></div>

                    <div class="panel panel-info">
                        <div class="panel-heading">Goals</div>
                        <div class="panel-body">

                            @foreach($v['beatTheBookie']['Goals'] as $goalsPrediction)
                                <div class="col-md-4">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">{{ $goalsPrediction->getTitle() }}</div>
                                        <div class="panel-body">
                                            <div class="progress">
                                                <div class="progress-bar {{ $goalsPrediction->getPercentage() > $goalsPrediction->opposite()->getPercentage() ? 'progress-bar-success' : 'progress-bar-danger' }}" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: {{ $goalsPrediction->getPercentage() }}%;">
                                                    {{ $goalsPrediction->getPercentage() }}%
                                                </div>
                                                <div class="progress-bar {{ $goalsPrediction->getPercentage() > $goalsPrediction->opposite()->getPercentage() ? 'progress-bar-danger' : 'progress-bar-success' }} " role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: {{ $goalsPrediction->opposite()->getPercentage() }}%;">
                                                    {{ $goalsPrediction->opposite()->getPercentage() }}%
                                                </div>
                                            </div>
                                            <div>
                                                <span>Odds:  {{ $goalsPrediction->getOdds() }}</span>
                                                <span class="float-right">Odds:  {{ $goalsPrediction->opposite()->getOdds() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="panel panel-primary" id="valueBetCalculatorContainer">
            <div class="panel-heading">Calculate Value Bet</div>
            <div class="panel-body">
                <form action="" method="POST">
                    <div class="form-group match-input">
                        <label class="match-input-label" for="odds">Add Bookamer Odds</label>
                        <input type="text" class="form-control" id="odds" placeholder="Odds">
                    </div>
                    <div class="form-group match-input">
                        <label class="match-input-label" for="probability">Add Win Probability</label>
                        <input type="text" class="form-control" id="probability" placeholder="Probability %">
                    </div>
                </form>
                <button type="submit" class="btn btn-primary" id="calculateValueBets">Calculate</button>
                <a id="back-to-top" href="#" class="btn btn-primary btn-lg back-to-top" role="button" title="Click to return on the top page" data-toggle="tooltip" data-placement="left"><span class="glyphicon glyphicon-chevron-up"></span></a>
            </div>
        </div>
    </div>
    @else
            <div class="row">
                <div class="jumbotron">
                    <h1>Check Predictions</h1>
                    <form action="" method="POST">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <a id="loadCountriesWithCompetitions">Load Countries With Competitions</a>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
    @endif
</div>
@endsection
@section('appScripts')
    @parent
    <script src="{{ mix('/js/views/home/index.js') }}"></script>
@endsection
