@extends('layouts.app')

@section('content')
<div class="container">
    <div  style="
            width: 1600px;
            position: relative;
            right: 150px;
    ">

        @if (count($errors))
            <ul class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        @if ($data)
        <a href="/home">Recalculate</a>
        <div class="panel panel-success">
            @foreach($data as $match => $v)
                <div class="panel-heading">{{ $match }}</div>

                <div class="panel-body">
                    <table border="1" class="table">
                        <tr>
                            <th>Home Win</th>
                            <th>Draw</th>
                            <th>Away Win</th>
                            <th colspan="2">Over/Under 1.5</th>
                            <th colspan="2">Over/Under 2.5</th>
                            <th colspan="2">Over/Under 3.5</th>
                            <th colspan="2">Over/Under 4.5</th>
                            <th colspan="2">Both Teams To Score</th>
                        </tr>
                        <tr class="more-visible">
                            <td>{{ $v['beatTheBookie']['Home Win']['odds'] }}/({{ $v['beatTheBookie']['Home Win']['percentage'] }}%)</td>
                            <td>{{ $v['beatTheBookie']['Draw']['odds'] }}/({{ $v['beatTheBookie']['Draw']['percentage'] }}%)</td>
                            <td>{{ $v['beatTheBookie']['Away Win']['odds'] }}/({{ $v['beatTheBookie']['Away Win']['percentage'] }}%)</td>
                            <td>
                                {{ $v['beatTheBookie']['Over/Under 1.5']['over 1.5']['odds'] }}/({{ $v['beatTheBookie']['Over/Under 1.5']['over 1.5']['percentage'] }}%)
                            </td>
                            <td>
                                {{ $v['beatTheBookie']['Over/Under 1.5']['under 1.5']['odds'] }}/({{ $v['beatTheBookie']['Over/Under 1.5']['under 1.5']['percentage'] }}%)
                            </td>
                            <td>
                                {{ $v['beatTheBookie']['Over/Under 2.5']['over 2.5']['odds'] }}/({{ $v['beatTheBookie']['Over/Under 2.5']['over 2.5']['percentage'] }}%)
                            </td>
                            <td>
                                {{ $v['beatTheBookie']['Over/Under 2.5']['under 2.5']['odds'] }}/({{ $v['beatTheBookie']['Over/Under 2.5']['under 2.5']['percentage'] }}%)
                            </td>
                            <td>
                                {{ $v['beatTheBookie']['Over/Under 3.5']['over 3.5']['odds'] }}/({{ $v['beatTheBookie']['Over/Under 3.5']['over 3.5']['percentage'] }}%)
                            </td>
                            <td>
                                {{ $v['beatTheBookie']['Over/Under 3.5']['under 3.5']['odds'] }}/({{ $v['beatTheBookie']['Over/Under 3.5']['under 3.5']['percentage'] }}%)
                            </td>
                            <td>
                                {{ $v['beatTheBookie']['Over/Under 4.5']['over 4.5']['odds'] }}/({{ $v['beatTheBookie']['Over/Under 4.5']['over 4.5']['percentage'] }}%)
                            </td>
                            <td>
                                {{ $v['beatTheBookie']['Over/Under 4.5']['under 4.5']['odds'] }}/({{ $v['beatTheBookie']['Over/Under 4.5']['under 4.5']['percentage'] }}%)
                            </td>
                            <td>
                                Yes - {{ $v['beatTheBookie']['Both Teams To Score']['Yes']['odds'] }}/({{ $v['beatTheBookie']['Both Teams To Score']['Yes']['percentage'] }}%)
                            </td>
                            <td>
                                No - {{ $v['beatTheBookie']['Both Teams To Score']['No']['odds'] }}/({{ $v['beatTheBookie']['Both Teams To Score']['No']['percentage'] }}%)
                            </td>
                        </tr>
                        <tr class="more-visible">
                            <td>1X - {{ $v['beatTheBookie']['1X']['odds'] }}/({{ $v['beatTheBookie']['1X']['percentage'] }}%)</td>
                            <td>X2 - {{ $v['beatTheBookie']['X2']['odds'] }}/({{ $v['beatTheBookie']['X2']['percentage'] }}%)</td>
                        </tr>
                    </table>
                </div>
               <div class="panel" id="valueBetCalculatorContainer">
                   <div class="panel-heading">Calculate Value Bet</div>
                   <div class="panel-body">
                       <form action="" method="POST">
                           <div class="form-group match-input">
                               <label class="match-input-label" id="labelMatch-1" for="match-1">Add Bookamer Odds</label>
                               <input type="text" class="form-control" id="odds" placeholder="Odds">
                           </div>
                           <div class="form-group match-input">
                               <label class="match-input-label" id="labelMatch-1" for="match-1">Add Win Probability</label>
                               <input type="text" class="form-control" id="probability" placeholder="Probability %">
                           </div>
                       </form>
                       <button type="submit" class="btn btn-primary" id="calculateValueBets">Calculate</button>
                   </div>
               </div>
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
                    <pre>{{ print_r($v['valueBets'], true) }}</pre>
                </div>
            @endforeach
        </div>
        @else
            <form action="" method="POST">
                {{ csrf_field() }}
                <div class="form-group match-input">
                    <label class="match-input-label" id="labelMatch-1" for="match-1">Add Match Game</label>
                    <input type="text" class="form-control" id="match-1" placeholder="Add match" name="match[1]" value="{{ old('match.1') }}">
                </div>
                <div class="form-group add-more-games">
                    <a id="addFootballMatches">Add More Games</a>
                </div>
                <div class="form-group">
                    <a id="loadCountriesWithCompetitions">Load Countries With Competitions</a>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        @endif
    </div>
</div>
@endsection
@section('appScripts')
    @parent
    <script src="{{ mix('/js/views/home/index.js') }}"></script>
@endsection
