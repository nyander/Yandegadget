@extends('layouts.app')

@section('content')
 <h3>Products</h3>
 @if(count($products) > 0)
    @foreach($products as $product)
    <div class="card" style="width:18rem;">
        <img class="card-img-top" src="{{$product->image}}"> 
        <div class="card-body">
        <h5 class="title"> <a href="/products/{{$product->id}}">{{$product->name}}</a></h5>
            <p class="card-text">Price: <span class= "pricesmbl">£</span>{{$product->selling_Price}}</p>
        </div>
    </div>
    @endforeach   
     {{$products->links()}}
 @else
    <p>No Products Available at this very moment</p>
 @endif
@endsection