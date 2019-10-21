<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{config('app.name','Yande Gadgets')}}</title> 
        <!–– the title will retrieved from the app folder, if not found, it should use 'Yande Gadgets'   ––>
    </head>
    <body>
        <div>
            <h1>The index page workds</h1>
            <p> I can now conirm that you are connected to the index page </p> 
        </div>
    </body>
</html>
