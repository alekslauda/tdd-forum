@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">

            @if (count($errors))
                <ul class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            @if ($data)
            <a href="/home">Recalculate</a>
            <div class="panel panel-success" style="width: 1400px">
                @foreach($data as $match => $v)
                    <div class="panel-heading">{{ $match }}</div>

                    <div class="panel-body">
                        <table class="table">
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
                                <td>{{ $v['beatTheBookie']['Home Win'] }}</td>
                                <td>{{ $v['beatTheBookie']['Draw'] }}</td>
                                <td>{{ $v['beatTheBookie']['Away Win'] }}</td>
                                <td>
                                    {{ $v['beatTheBookie']['Over/Under 1.5']['over 1.5'] }}
                                </td>
                                <td>
                                    {{ $v['beatTheBookie']['Over/Under 1.5']['under 1.5'] }}
                                </td>
                                <td>
                                    {{ $v['beatTheBookie']['Over/Under 2.5']['over 2.5'] }}
                                </td>
                                <td>
                                    {{ $v['beatTheBookie']['Over/Under 2.5']['under 2.5'] }}
                                </td>
                                <td>
                                    {{ $v['beatTheBookie']['Over/Under 3.5']['over 3.5'] }}
                                </td>
                                <td>
                                    {{ $v['beatTheBookie']['Over/Under 3.5']['under 3.5'] }}
                                </td>
                                <td>
                                    {{ $v['beatTheBookie']['Over/Under 4.5']['over 4.5'] }}
                                </td>
                                <td>
                                    {{ $v['beatTheBookie']['Over/Under 4.5']['under 4.5'] }}
                                </td>
                                <td>
                                    Yes - {{ $v['beatTheBookie']['Both Teams To Score']['Yes'] }}
                                </td>
                                <td>
                                    No - {{ $v['beatTheBookie']['Both Teams To Score']['No'] }}
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="panel-body">
                        <table class="table">
                            <tr>
                                <th colspan="2" class="panel-heading">Correct Score</th>
                            </tr>
                            @foreach($v['beatTheBookie']['Correct Score'] as $res => $chance)
                               <tr class="{{ $chance['flagged'] ? 'table-success' : ''}}">
                                   <th>{{ $res }}</th>
                                   <td>{{ $chance['stats'] }}</td>
                               </tr>
                            @endforeach
                        </table>
                    </div>
                @endforeach
            </div>
            @else
                <form action="" method="POST">
                    {{ csrf_field() }}
                    <div class="form-group">
                        @foreach(range(1, 10) as $input)
                        <label for="match-{{ $input }}">Match {{ $input }}</label>
                        <input type="text" class="form-control" id="match-{{ $input }}" placeholder="Add match" name="match[{{$input}}]" value="{{ old('match.' . $input) }}">
                        @endforeach
                    </div>
                    {{--<div class="form-group">--}}
                        {{--<label for="exampleInputText1">Competition Url(soccerway)</label>--}}
                        {{--<input type="text" class="form-control" id="exampleInputText1" placeholder="Paste url with statistic" name="soccerway_competition_url" value="{{ old('soccerway_competition_url') }}">--}}
                    {{--</div>--}}
                    <div class="form-group">
                        <label for="exampleFormControlSelect3">Statistic Interval</label>
                        <select class="form-control" id="exampleFormControlSelect3" name="past_year">
                            <option value="0" selected>Current Year</option>
                            @foreach([1 => 'Past Year', 2 => 'Past Two Years', 3 => 'Past Three Years'] as $yearNum => $text)
                                <option value="{{$yearNum}}" {{ $yearNum == old('past_year') ? "selected=selected" : "" }}>{{ $text }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <a id="loadCountriesWithCompetitions">Load Countries With Competitions</a>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            @endif
        </div>
    </div>

</div>
@endsection
@section('appScripts')
    @parent
    <script src="{{ mix('/js/views/home/index.js') }}"></script>
@endsection
