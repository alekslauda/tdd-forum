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
                            <div class="col-md-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">Home Win</div>
                                    <div class="panel-body">
                                        <div class="progress">
                                            <div class="progress-bar {{ $v['beatTheBookie']['Home Win']['percentage'] > ($v['beatTheBookie']['Draw']['percentage'] + $v['beatTheBookie']['Away Win']['percentage']) ? 'progress-bar-success' : ''}}" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: {{ $v['beatTheBookie']['Home Win']['percentage'] }}%;">
                                                {{ $v['beatTheBookie']['Home Win']['percentage'] }}%
                                            </div>
                                        </div>
                                        <span>Odds:  {{ $v['beatTheBookie']['Home Win']['odds'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">Draw</div>
                                    <div class="panel-body">
                                        <div class="progress">
                                            <div class="progress-bar {{ $v['beatTheBookie']['Draw']['percentage'] > ($v['beatTheBookie']['Home Win']['percentage'] + $v['beatTheBookie']['Away Win']['percentage']) ? 'progress-bar-success' : ''}}" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: {{ $v['beatTheBookie']['Draw']['percentage'] }}%;">
                                                {{ $v['beatTheBookie']['Draw']['percentage'] }}%
                                            </div>
                                        </div>
                                        <span>Odds:  {{ $v['beatTheBookie']['Draw']['odds'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">Away Win</div>
                                    <div class="panel-body">
                                        <div class="progress">
                                            <div class="progress-bar {{ $v['beatTheBookie']['Away Win']['percentage'] > ($v['beatTheBookie']['Draw']['percentage'] + $v['beatTheBookie']['Home Win']['percentage']) ? 'progress-bar-success' : ''}}" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: {{ $v['beatTheBookie']['Away Win']['percentage'] }}%;">
                                                {{ $v['beatTheBookie']['Away Win']['percentage'] }}%
                                            </div>
                                        </div>
                                        <span>Odds:  {{ $v['beatTheBookie']['Away Win']['odds'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">Home Win Or Draw / (1X)</div>
                                    <div class="panel-body">
                                        <div class="progress">
                                            <div class="progress-bar {{ $v['beatTheBookie']['1X']['percentage'] > ($v['beatTheBookie']['X2']['percentage']) ? 'progress-bar-success' : ''}}" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: {{ $v['beatTheBookie']['1X']['percentage'] }}%;">
                                                {{ $v['beatTheBookie']['1X']['percentage'] }}%
                                            </div>
                                        </div>
                                        <span>Odds:  {{ $v['beatTheBookie']['1X']['odds'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">Away Win Or Draw / (X2)</div>
                                    <div class="panel-body">
                                        <div class="progress">
                                            <div class="progress-bar {{ $v['beatTheBookie']['X2']['percentage'] > ($v['beatTheBookie']['1X']['percentage']) ? 'progress-bar-success' : ''}}" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: {{$v['beatTheBookie']['X2']['percentage']}}%;">
                                                {{ $v['beatTheBookie']['X2']['percentage'] }}%
                                            </div>
                                        </div>
                                        <span>Odds:  {{ $v['beatTheBookie']['X2']['odds'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="clearfix"></div>

                    <div class="panel panel-info">
                        <div class="panel-heading">Goals</div>
                        <div class="panel-body">
                            <div class="col-md-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">Over/Under 1.5</div>
                                    <div class="panel-body">
                                        <div class="progress">
                                            <div class="progress-bar {{ $v['beatTheBookie']['Over/Under 1.5']['over 1.5']['percentage'] > (100 - $v['beatTheBookie']['Over/Under 1.5']['over 1.5']['percentage'])? 'progress-bar-success' : 'progress-bar-danger' }}" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: {{ $v['beatTheBookie']['Over/Under 1.5']['over 1.5']['percentage'] }}%;">
                                                {{ $v['beatTheBookie']['Over/Under 1.5']['over 1.5']['percentage'] }}%
                                            </div>
                                            <div class="progress-bar {{ $v['beatTheBookie']['Over/Under 1.5']['over 1.5']['percentage'] > (100 - $v['beatTheBookie']['Over/Under 1.5']['over 1.5']['percentage'])? 'progress-bar-danger' : 'progress-bar-success' }} " role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: {{ (100 - $v['beatTheBookie']['Over/Under 1.5']['over 1.5']['percentage']) }}%;">
                                                {{ (100 - $v['beatTheBookie']['Over/Under 1.5']['over 1.5']['percentage']) }}%
                                            </div>
                                        </div>
                                        <div>
                                            <span>Odds:  {{ $v['beatTheBookie']['Over/Under 1.5']['over 1.5']['odds'] }}</span>
                                            <span class="float-right">Odds:  {{ $v['beatTheBookie']['Over/Under 1.5']['under 1.5']['odds'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">Over/Under 2.5</div>
                                    <div class="panel-body">
                                        <div class="progress">
                                            <div class="progress-bar {{ $v['beatTheBookie']['Over/Under 2.5']['over 2.5']['percentage'] > (100 - $v['beatTheBookie']['Over/Under 2.5']['over 2.5']['percentage'])? 'progress-bar-success' : 'progress-bar-danger' }}" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: {{ $v['beatTheBookie']['Over/Under 2.5']['over 2.5']['percentage'] }}%;">
                                                {{ $v['beatTheBookie']['Over/Under 2.5']['over 2.5']['percentage'] }}%
                                            </div>
                                            <div class="progress-bar {{ $v['beatTheBookie']['Over/Under 2.5']['over 2.5']['percentage'] > (100 - $v['beatTheBookie']['Over/Under 2.5']['over 2.5']['percentage'])? 'progress-bar-danger' : 'progress-bar-success' }} " role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: {{ (100 - $v['beatTheBookie']['Over/Under 2.5']['over 2.5']['percentage']) }}%;">
                                                {{ (100 - $v['beatTheBookie']['Over/Under 2.5']['over 2.5']['percentage']) }}%
                                            </div>
                                        </div>
                                        <div>
                                            <span>Odds:  {{ $v['beatTheBookie']['Over/Under 2.5']['over 2.5']['odds'] }}</span>
                                            <span class="float-right">Odds:  {{ $v['beatTheBookie']['Over/Under 2.5']['under 2.5']['odds'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">Over/Under 3.5</div>
                                    <div class="panel-body">
                                        <div class="progress">
                                            <div class="progress-bar {{ $v['beatTheBookie']['Over/Under 3.5']['over 3.5']['percentage'] > (100 - $v['beatTheBookie']['Over/Under 3.5']['over 3.5']['percentage'])? 'progress-bar-success' : 'progress-bar-danger' }}" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: {{ $v['beatTheBookie']['Over/Under 3.5']['over 3.5']['percentage'] }}%;">
                                                {{ $v['beatTheBookie']['Over/Under 3.5']['over 3.5']['percentage'] }}%
                                            </div>
                                            <div class="progress-bar {{ $v['beatTheBookie']['Over/Under 3.5']['over 3.5']['percentage'] > (100 - $v['beatTheBookie']['Over/Under 3.5']['over 3.5']['percentage'])? 'progress-bar-danger' : 'progress-bar-success' }} " role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: {{ (100 - $v['beatTheBookie']['Over/Under 3.5']['over 3.5']['percentage']) }}%;">
                                                {{ (100 - $v['beatTheBookie']['Over/Under 3.5']['over 3.5']['percentage']) }}%
                                            </div>
                                        </div>
                                        <div>
                                            <span>Odds:  {{ $v['beatTheBookie']['Over/Under 3.5']['over 3.5']['odds'] }}</span>
                                            <span class="float-right">Odds:  {{ $v['beatTheBookie']['Over/Under 3.5']['under 3.5']['odds'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">Over/Under 4.5</div>
                                    <div class="panel-body">
                                        <div class="progress">
                                            <div class="progress-bar {{ $v['beatTheBookie']['Over/Under 4.5']['over 4.5']['percentage'] > (100 - $v['beatTheBookie']['Over/Under 4.5']['over 4.5']['percentage'])? 'progress-bar-success' : 'progress-bar-danger' }}" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: {{ $v['beatTheBookie']['Over/Under 4.5']['over 4.5']['percentage'] }}%;">
                                                {{ $v['beatTheBookie']['Over/Under 4.5']['over 4.5']['percentage'] }}%
                                            </div>
                                            <div class="progress-bar {{ $v['beatTheBookie']['Over/Under 4.5']['over 4.5']['percentage'] > (100 - $v['beatTheBookie']['Over/Under 4.5']['over 4.5']['percentage'])? 'progress-bar-danger' : 'progress-bar-success' }} " role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: {{ (100 - $v['beatTheBookie']['Over/Under 4.5']['over 4.5']['percentage']) }}%;">
                                                {{ (100 - $v['beatTheBookie']['Over/Under 4.5']['over 4.5']['percentage']) }}%
                                            </div>
                                        </div>
                                        <div>
                                            <span>Odds:  {{ $v['beatTheBookie']['Over/Under 4.5']['over 4.5']['odds'] }}</span>
                                            <span class="float-right">Odds:  {{ $v['beatTheBookie']['Over/Under 4.5']['under 4.5']['odds'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="clearfix"></div>

                    <div class="panel panel-info">
                        <div class="panel-heading">Both Teams Can Score</div>
                        <div class="panel-body">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">Yes/No</div>
                                    <div class="panel-body">
                                        <div class="progress">
                                            <div class="progress-bar {{ $v['beatTheBookie']['Both Teams To Score']['Yes']['percentage'] > (100 - $v['beatTheBookie']['Both Teams To Score']['Yes']['percentage']) ? 'progress-bar-success' : 'progress-bar-danger'}}" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: {{ $v['beatTheBookie']['Both Teams To Score']['Yes']['percentage'] }}%;">
                                                {{ $v['beatTheBookie']['Both Teams To Score']['Yes']['percentage'] }}%
                                            </div>
                                            <div class="progress-bar {{ $v['beatTheBookie']['Both Teams To Score']['Yes']['percentage'] > (100 - $v['beatTheBookie']['Both Teams To Score']['Yes']['percentage']) ? 'progress-bar-danger' : 'progress-bar-success'}}" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: {{ (100 - $v['beatTheBookie']['Both Teams To Score']['Yes']['percentage']) }}%;">
                                                {{ (100 - $v['beatTheBookie']['Both Teams To Score']['Yes']['percentage']) }}%
                                            </div>
                                        </div>
                                        <div>
                                            <span>Odds:  {{ $v['beatTheBookie']['Both Teams To Score']['Yes']['odds'] }}</span>
                                            <span class="float-right">Odds:  {{ $v['beatTheBookie']['Both Teams To Score']['No']['odds'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="clearfix"></div>

                    <div class="panel-body">
                        <table class="table">
                            <tr>
                                <th colspan="2" class="panel-heading">Correct Score</th>
                            </tr>
                            @foreach($v['beatTheBookie']['Correct Score'] as $res => $chance)
                                <tr class="{{ $chance['flagged'] ? 'table-success' : ''}}">
                                    <th>{{ $res }}</th>
                                    <td>{{ $chance['odds'] }}/({{ $chance['percentage'] }}%)</td>
                                </tr>
                            @endforeach
                        </table>
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
