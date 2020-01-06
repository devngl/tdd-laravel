<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'TicketBeast')</title>

    <script src="{{ asset('js/app.js') }}" defer></script>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @include('scripts.app')
</head>
<body class="bg-dark">
<div id="app">
    @yield('body')
</div>

@stack('beforeScripts')
@stack('afterScripts')
{{ svg_spritesheet() }}
</body>
</html>
