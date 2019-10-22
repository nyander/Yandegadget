<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Yande Gadgets</title> 
       
    </head>
    <body>
        <div>
            {{-- this is will return the value and saves the content in its state --}}      
            @yield('content')
        </div>
    </body>
</html>
