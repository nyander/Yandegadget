@extends('layouts.app')

@section('content')

    <div class="sidebar">
        <h3>Category</h3>
        <ul>
            @foreach ($categories as $category)
                <li class="{{ request()->category == $category->id ? 'active' : ''}}"><a href="{{route('products.index', ['category' => $category->id] )}}">{{$category->type}}</a></li>
            @endforeach
        </ul>
    </div>

    <div class="products-header"> 
        <h2 class="stylish-heading"></h2>
        <div>
            <strong class="stylish-heading">Price</strong>
            <a href="{{route('products.index', ['category' => request()->category, 'sort'=> 'low_high'] )}}">Low to High</a>
            <a href="{{route('products.index', ['category' => request()->category, 'sort'=> 'high_low'] )}}">High to Low</a>
        </div>
    </div>

    <h3>{{$categoryname}}</h3>
    @forelse ($products as $product)
        <div class="card" style="width:18rem;">
        
            <div class="card" style="width:18rem;">
                <img class="card-img-top" src="{{$product->image}}"> 
                <div class="card-body">
                    <h5 class="title"> <a href="/products/{{$product->id}}">{{$product->name}}</a></h5>
                    <p class="card-text">Price: <span class= "pricesmbl">£</span>{{$product->selling_Price}}</p>
                </div>
                
            </div>
        </div>
        
        
    @empty
    <div  style="text-align: left"> No Items found</div>
    @endforelse
    
    {{$products->appends(request()->input())->links()}}
@endsection