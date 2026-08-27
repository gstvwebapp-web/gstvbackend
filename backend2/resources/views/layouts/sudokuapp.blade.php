<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ config('app.url') }}">
    <link rel="icon" href="{{ config('app.url') }}/public/assets/images/favicon.png">
    <title>@yield('meta-title')</title>
    <script src="{{ URL::asset('public/assets/js/jquery-3.6.0.min.js')}}"></script>   
</head>
<body>
    @yield('content')
</body>
</html>