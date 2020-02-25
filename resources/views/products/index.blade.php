@extends('layouts.app')

@section('content')

@if(Gate::allows('manage-products'))
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
        @if($product->recieved == true)   
            @if($product->sold ==true)
                <div class="card" style="background-color: #C0D9AF; border-width: 5px; ">
                    
                    <div class="card" style=" background-color: #DCF4CC;">
                        <img class="card-img-top" src="{{$product->image}}"> 
                        <div class="card-body">
                            <h5 class="title"> <a href="/products/{{$product->id}}">{{$product->name}}</a></h5>
                            <p class="card-text">Price: <span class= "pricesmbl">£</span>{{$product->selling_Price}}</p>
                            <h6>SOLD<h6>
                        </div>
                        
                    </div>
                </div>
         @elseif ($product->sold ==false) 
            <div class="card" style=" background-color: #C0D9AF; border-width: 5px;">
            
                <div class="card" style=" ">
                    <img class="card-img-top" src="{{$product->image}}"> 
                    <div class="card-body">
                        <h5 class="title"> <a href="/products/{{$product->id}}">{{$product->name}}</a></h5>
                        <p class="card-text">Price: <span class= "pricesmbl">£</span>{{$product->selling_Price}}</p>
                        <h6>SHIPPED<h6>
                    </div>
                    
                </div>
            </div>
            @endif
        @elseif($product->recieved == false)
        <div class="card" style=" border-width: 5px;">
        
            <div class="card" style="">
                <img class="card-img-top" src="{{$product->image}}"> 
                <div class="card-body">
                    <h5 class="title"> <a href="/products/{{$product->id}}">{{$product->name}}</a></h5>
                    <p class="card-text">Price: <span class= "pricesmbl">£</span>{{$product->selling_Price}}</p>
                    
                </div>
                
            </div>
        </div>
        @endif
        
    @empty
    <div  style="text-align: left"> No Items found</div>
    @endforelse
    
    {{$products->appends(request()->input())->links()}}

{{-- Staff Member View --}}
@elseif(Gate::allows('sub-manage-products'))
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
 @if($product->recieved == true)
    @if($product->sold ==false)        
        <div class="card" style="">
        
            <div class="card" style="" >
                <img class="card-img-top" src="{{$product->image}}"> 
                <div class="card-body">
                    <h5 class="title"> <a href="/products/{{$product->id}}">{{$product->name}}</a></h5>
                    <p class="card-text">Price: <span class= "pricesmbl">£</span>{{$product->selling_Price}}</p>
                    <a href="/products/{{$product->id}}/purchase"> <button class="btn btn-primary"> Purchase </button> </a>
                </div>
                
            </div>
        </div>

    @elseif($product->sold ==true)
    <div class="card" style=" ">
        
        <div class="card" style=" background-color: #DCF4CC;">
            <img class="card-img-top" src="{{$product->image}}"> 
            <div class="card-body">
                <h5 class="title"> <a href="/products/{{$product->id}}">{{$product->name}}</a></h5>
                <p class="card-text">Price: <span class= "pricesmbl">£</span>{{$product->selling_Price}}</p>
                <h6>SOLD<h6>
            </div>
            
        </div>
    </div>
    @endif 
@endif    
    
@empty
<div  style="text-align: left"> No Items found</div>
@endforelse

{{$products->appends(request()->input())->links()}}


{{-- Customer and Not Logged In users View --}}
@else
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
     @if($product->recieved == true)
        @if($product->sold == false)        
            <div class="card" style="">
            
                <div class="card" style="">
                    <img class="card-img-top" src="{{$product->image}}"> 
                    <div class="card-body">
                        <h5 class="title"> <a href="/products/{{$product->id}}">{{$product->name}}</a></h5>
                        <p class="card-text">Price: <span class= "pricesmbl">£</span>{{$product->selling_Price}}</p>
                    </div>
                    
                </div>
            </div>
        @endif
    @endif    
        
    @empty
    <div  style="text-align: left"> No Items found</div>
    @endforelse
    
    {{$products->appends(request()->input())->links()}}
@endif    
@endsection