{{-- extend is basically extending another class using the @extend function which is extending the app file in the layout folder  --}}
@extends('layouts.app')

{{--inside the @section is what will be added in the app file, basically content to be added to your template--}}
@section('content')
{{-- $title is the variable name in which the Postcontroller will replace with the variable $tite - so this title will be variable in the postcontroller    --}}
    <div class="jumbotron text-center"> 
        <h1>{{$title}}</h1>
        <p> I can now conirm that you are connected to the index page hello serve</p> 
        <p><a class=" btn btn-primary btn-lg" href="/login" role="button">Login</a> <a class=" btn btn-primary btn-lg" href="/signup" role="button">Sign-up</a>
    </div>     
@endsection

