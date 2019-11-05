<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="{{asset('css/app.css')}}"/>
        <title>Yande Gadgets</title>         
    </head>
    <body>
        {{--we are including the navbar into the website which is located in the inc folder --}}
        @include('inc.navbar')
        <div class="container">
            {{-- this is will return the value and saves the content in its state --}}      
            @yield('content')
        </div>
    </body>
</html>
