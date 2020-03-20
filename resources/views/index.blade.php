{{-- extend is basically extending another class using the @extend function which is extending the app file in the layout folder  --}}
@extends('layouts.app')

{{--inside the @section is what will be added in the app file, basically content to be added to your template--}}
@section('index')
{{-- $title is the variable name in which the Postcontroller will replace with the variable $tite - so this title will be variable in the postcontroller    --}}

    <div class="header-banner">
        <h1 class="index-title">{{$title}}</h1>
        <p class="index-subtitle"> I can now conirm that you are connected to the index page hello serve</p> 
        @if (Auth::check()) 
            <p class="index-subtitle"> Logged In as: {{Auth::user()->name}}
        @else 
        <p><a class=" btn btn-danger btn-lg" href="/login" role="button">Login</a> <a class=" btn btn-success btn-lg" href="/signup" role="button">Sign-up</a>
        @endif
    </div>
    <br>
    <br>
    <div class="container">
      <h1 class="text-center">This is a test</h1>
      <div class="container">
        <p class="text-center">The client has been running a technology business for a few years. However, 
                                it has not been able to grow further since the current business 
                                process is manual-driven and has not taken advantage of the recent global technological developments. 
        </p>
      </div>
      <div class="col text-center">
        <button type="button" class="btn btn-success ">Find Out More</button>
      </div>            
    </div>

    <br>
    <br>

    <div class="album py-5 bg-light">
        <div class="container">

          <div class="row">
            <div class="col-md-4">
              <div class="card mb-4 box-shadow pt-5" id="phones">                             
                <div class="card-body pt-5">
                  <h5 class="index card-text text-light">Phones</h5>  
                  <p class="index card-text text-light">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="btn-group">
                      <button type="button" class="btn btn-sm btn-danger">View</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card mb-4 box-shadow pt-5" id="tvaudio">
                <div class="card-body pt-5">
                  <h5 class="index card-text text-light">TV & Audio</h5>  
                  <p class="index card-text text-light">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="btn-group">
                      <button type="button" class="btn btn-sm btn-danger">View</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card mb-4 box-shadow pt-5" id="computer">                
                <div class="card-body pt-5">
                  <h5 class="index card-text text-light">Computer</h5> 
                  <p class="index card-text text-light">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="btn-group">
                      <button type="button" class="btn btn-sm btn-danger">View</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </div>



    
            
@endsection

