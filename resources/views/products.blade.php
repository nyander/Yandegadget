{{-- extend is basically extending another class using the @extend function which is extending the app file in the layout folder  --}}
@extends('layouts.app')

{{--inside the @section is what will be added in the app file, basically content to be added to your template--}}
@section('content')
            <h1>{{$title}}</h1>
                {{--this is a loop which if the product list is 0, then it will return each of the products--}} 
    @if(count($productslist) > 0)
    <ul class="list-group">
        @foreach($productslist as $product)
            <li class="list-group-item">{{$product}}</li>
        @endforeach
    </ul>    
    @endif
@endsection