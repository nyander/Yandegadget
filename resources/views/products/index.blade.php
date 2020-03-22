@extends('layouts.app')

@section('content')



{{-- Sidebar --}}
    <div class="sidebar">
        <h3>Category</h3>        
        <ul>
            @foreach ($categories as $category)
                <li class="{{ request()->category == $category->id ? 'active' : ''}}"><a href="{{route('products.index', ['category' => $category->id] )}}">{{$category->type}}</a></li>
            @endforeach
        </ul>
    </div>
    {{-- Sort By on the right --}}
    <div class="products-header float-right">         
        <div >
            <strong class="stylish-heading">Price</strong>
            <a href="{{route('products.index', ['category' => request()->category, 'sort'=> 'low_high'] )}}">Low to High</a>
            <a href="{{route('products.index', ['category' => request()->category, 'sort'=> 'high_low'] )}}">High to Low</a>
        </div>
    </div>

    


    {{-- Products Section --}}

    @if(Gate::allows('manage-products'))
        {{-- Displayed Category Name --}}
        <h3>{{$categoryname}}</h3>
        <div class="album py-5 bg-light">
            <div class="container">
                <div class="row">
                    @forelse ($products as $product)

                        <div hidden style="display: none;">
                            @if($product->recieved == true)   
                                @if($product->sold ==true)
                                    {{$color = '#DCF4CC'}}
                                    {{$status = 'SOLD'}}
                                @elseif ($product->sold ==false)
                                    {{$color = '#FFFFFF'}}
                                    {{$status = 'SHIPPED'}}
                                @endif
                            @elseif($product->recieved == false)
                                {{$color = '#D3D3D3'}}
                                {{$status = 'NOT SHIPPED'}}
                            @endif
                        </div>
                        
                                        
                        <div class="col-md-4" >
                            <div class="card mb-4 box-shadow" style=" background-color: {{$color}};">
                                <img class="card-img-top" src="/gallery/{{$product->thumbnail_path}}" >
                                <div class="card-body">
                                    <h5 class="title"> {{$product->name}}</h5>
                                    <p class="card-text">Price: <span class= "pricesmbl">£</span>{{$product->selling_Price}}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="btn-group">
                                            <a href="/products/{{$product->id}}"><button type="button" class="btn btn-sm btn-outline-secondary">View</button></a>
                                            @if($product->recieved == false)
                                            <form action="{{route('shipments.store')}}" method="POST">
                                                @csrf
                                                <input type="hidden" name="id" value="{{$product->id}}">
                                                <input type="hidden" name="name" value="{{$product->name}}">
                                                <input type="hidden" name="selling_Price" value="{{$product->selling_Price}}">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary">Add To Cart</button>
                                            </form>
                                            @endif
                                        </div>
                                        <small class="text-muted">{{$status}}</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @empty
                    
                        {{-- if there is no item within that category --}}
                        <div  style="text-align: left"> No Items found</div>
                
                    @endforelse
                </div>
            </div>
        </div>
        
        {{$products->appends(request()->input())->links()}}

    {{-- Staff Member View --}}
    @elseif(Gate::allows('sub-manage-products'))

        <h3>{{$categoryname}}</h3>
        @forelse ($products as $product)

        <div hidden style="display: none;">
              
            @if($product->sold ==true)
                {{$color = '#DCF4CC'}}
                {{$status = 'SOLD'}}
            @elseif ($product->sold ==false)
                {{$color = '#FFFFFF'}}
                {{$status = 'SHIPPED'}}
            @endif
           
        </div>
        @if($product->recieved == true)
            <div class="col-md-4" >
                <div class="card mb-4 box-shadow" style=" background-color: {{$color}};">
                    <img class="card-img-top" src="/gallery/{{$product->thumbnail_path}}" >
                    <div class="card-body">
                        <h5 class="title"> {{$product->name}}</h5>
                        <p class="card-text">Price: <span class= "pricesmbl">£</span>{{$product->selling_Price}}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="btn-group">
                                <a href="/products/{{$product->id}}"><button type="button" class="btn btn-sm btn-outline-secondary">View</button></a>
                                @if($product->sold == false)
                                <a href="/products/{{$product->id}}/purchase"> <button class="btn btn-sm btn-outline-secondary"> Purchase </button> </a>
                                @endif                            
                            </div>
                            <small class="text-muted">{{$status}}</small>
                        </div>
                    </div>
                </div>
            </div>
        @endif 

        @empty
        <div  style="text-align: left"> No Items found</div>
        @endforelse

        {{$products->appends(request()->input())->links()}}

        {{-- Customer and Not Logged In users View --}}
    @else    

        <h3>{{$categoryname}}</h3>
        @forelse ($products as $product)
        @if($product->recieved == true)
            @if($product->sold == false)        
            <div class="col-md-4" >
                <div class="card mb-4 box-shadow" >
                    <img class="card-img-top" src="/gallery/{{$product->thumbnail_path}}" >
                    <div class="card-body">
                        <h5 class="title"> {{$product->name}}</h5>
                        <p class="card-text">Price: <span class= "pricesmbl">£</span>{{$product->selling_Price}}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="btn-group">
                                <a href="/products/{{$product->id}}"><button type="button" class="btn btn-sm btn-outline-secondary">View</button></a>
                                
                            </div>
                            <small class="text-muted">AVAILABLE</small>
                        </div>
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
<script type="text/javascript">
    //to add more records  
    //    document.addEventListener('DOMContentLoaded', function () {
    //     $('.carousel').carousel();
    //     });
   </script>