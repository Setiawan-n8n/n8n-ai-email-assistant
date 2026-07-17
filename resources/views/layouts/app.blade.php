<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Simulasi Perjalanan KA - Stasiun Surabaya Gubeng')</title>
    <link rel="stylesheet" href="{{ asset('css/simulation.css') }}">
    @stack('head')
</head>
<body>
    @yield('content')
    @stack('scripts')
</body>
</html>
