@extends('layouts.app')

@section('content')


    <div class="py-5">
        <div class="row">
            <div class="container">
                <div class="products-header float-left">         
                    <p style="max-width:410em; ">Currys is a British electrical retailer operating in the United Kingdom and Republic of Ireland, owned by Dixons Carphone. It specialises in selling home electronics and household appliances, with 295 megastores and 73 high street shops.</p>
                </div> 
                {{-- Sort By on the right --}}
                <div class="products-header float-right">         
                    <div >
                        <strong class="stylish-heading">Price</strong>
                        <a href="{{route('products.index', ['category' => request()->category, 'sort'=> 'low_high'] )}}">Low to High</a>
                        <a href="{{route('products.index', ['category' => request()->category, 'sort'=> 'high_low'] )}}">High to Low</a>
                    </div>
                </div> 
            </div>
        </div>
    </div>    

    <div class="row">

        {{-- Sidebar --}}
        <div class="col-md-2 order-md-1 mr-auto">
            <div class="card">
                <ul class="list-group" style="border-left-style: solid; border-left-width: thick; border-color:#FFC107">
                    <a  href="/products"><li class="list-group-item bg-warning text-dark"><b>Category</b></li></a>               
                    @foreach ($categories as $category)
                        <a  href="{{route('products.index', ['category' => $category->id] )}}"><li class="{{ request()->category == $category->id ? 'active' : ''}} list-group-item">{{$category->type}}</li></a>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Products Section --}}

        @if(Gate::allows('admin-role'))
            {{-- Displayed Category Name --}}
            <div class="col-md-10 order-md-2">
                <h3>{{$categoryname}}</h3>
                <div class="sweat py-5 bg-light">
                    <div class="container">
                        <div class="row">
                            @forelse ($products as $product)

                                <div hidden style="display: none;">
                                    @if($product->received == true)   
                                        @if($product->sold ==true)
                                            {{$color = '#DCF4CC'}}
                                            {{$status = 'SOLD'}}
                                        @elseif ($product->sold ==false)
                                            {{$color = '#FFFFFF'}}
                                            {{$status = 'SHIPPED'}}
                                        @endif
                                    @elseif($product->received == false)
                                        {{$color = '#D3D3D3'}}
                                        {{$status = 'NOT SHIPPED'}}
                                    @endif
                                </div>
                                
                                                
                                <div class="col-md-4" >
                                    <div class="card mb-4 box-shadow" style=" background-color: {{$color}};">
                                        <img class="card-img-top " src="/gallery/{{$product->thumbnail_path}}" style="max-height:10em; object-fit: cover;" >
                                        <div class="card-body">
                                            <h5 class="title"> {{$product->name}}</h5>
                                            <p class="card-text">Price: <span class= "pricesmbl">£</span>{{$product->selling_Price}}</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="btn-group">
                                                    <a href="/products/{{$product->id}}"><button type="button" class="btn btn-sm btn-outline-secondary">View</button></a>
                                                    @if($product->received == false)
                                                    <form action="{{route('shipments.store')}}" method="POST" onsubmit="myButton.disabled = true; return true;">
                                                        @csrf
                                                        <input type="hidden" name="id" value="{{$product->id}}">
                                                        <input type="hidden" name="name" value="{{$product->name}}">
                                                        <input type="hidden" name="selling_Price" value="{{$product->selling_Price}}">
                                                        @can("admin-role")
                                                            <button type="submit" name="myButton" class="btn btn-sm btn-outline-primary">Ship Product</button>
                                                        @endcan    
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
        </div>
            
            {{$products->appends(request()->input())->links()}}

        {{-- Staff Member View --}}
        @elseif(Gate::allows('staff-role'))
        <div class="col-md-10 order-md-2">
            <h3>{{$categoryname}}</h3>
            <div class="album py-5 bg-light">
                <div class="container">
                    <div class="row">
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
                            @if($product->received == true)
                                <div class="col-md-4" >
                                    <div class="card mb-4 box-shadow" style=" background-color: {{$color}};">
                                        <img class="card-img-top" src="/gallery/{{$product->thumbnail_path}}" style="max-height:10em; object-fit: cover;">
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
                    </div>
                </div>
            </div>
        </div>

            {{$products->appends(request()->input())->links()}}

            {{-- Customer and Not Logged In users View --}}
        @else    
        <div class="col-md-10 order-md-2">
            <h3>{{$categoryname}}</h3>
            <div class="album py-5 bg-light">
                <div class="container">
                    <div class="row">
                        @forelse ($products as $product)
                        @if($product->received == true)
                            @if($product->sold == false)        
                            <div class="col-md-4" >
                                <div class="card mb-4 box-shadow" >
                                    <img class="card-img-top" src="/gallery/{{$product->thumbnail_path}}" style="max-height:10em; object-fit: cover;">
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
                    </div>
                </div>
            </div> 
        </div>       
            {{$products->appends(request()->input())->links()}}
        @endif    
    </div>
@endsection
<script type="text/javascript">
    //to add more records  
    //    document.addEventListener('DOMContentLoaded', function () {
    //     $('.carousel').carousel();
    //     });
   </script>