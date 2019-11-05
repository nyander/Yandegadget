<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">       
        <meta name="author" content="Richard Nyande">
        
        <title>Yande Gadgets</title>
        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway:100,600" type="text/css">
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">               
    
        <!--styles -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
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
