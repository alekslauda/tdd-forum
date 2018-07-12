<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Forum') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <style>
        body { padding-bottom: 100px }
        .level { display: flex; align-items: center; }
        .flex { flex: 1 }
        tr.more-visible> td {
            white-space: nowrap;
            font-size: 15px;
            color: #230707;
        }
        tr.table-success {
            background-color: lightgreen;
        }

        td.row-background-color {
            background-color: lightgreen;
        }

        .loader {
            border: 16px solid #f3f3f3; /* Light grey */
            border-top: 16px solid #3498db; /* Blue */
            border-radius: 50%;
            width: 120px;
            height: 120px;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

</head>
<body style="padding-bottom: 100px;">
<div id="app">
    @include('layouts.nav')

    @yield('content')
</div>

<!-- Scripts -->


@section('appScripts')
    <script
            src="https://code.jquery.com/jquery-3.3.1.min.js"
            integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
            crossorigin="anonymous"></script>
    <script src="{{ asset('js/app.js') }}"></script>
@show

@yield('pageScripts')

</body>
</html>
