{{-- extend is basically extending another class using the @extend function which is extending the app file in the layout folder  --}}
@extends('layouts.app')

{{--inside the @section is what will be added in the app file, basically content to be added to your template--}}
@section('content')
            <h1>The About page works</h1>
            <p> I can now conirm that you are connected to the index page </p> 
@endsection
