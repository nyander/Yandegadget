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
                        <img src="/gallery/{{$product->thumbnail_path}}">
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
                    <img src="/gallery/{{$product->thumbnail_path}}"> 
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
            <div class="card-body">                
                    <img src="/gallery/{{$product->thumbnail_path}}">
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
                <img src="/gallery/{{$product->thumbnail_path}}">
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
            <img src="/gallery/{{$product->thumbnail_path}}">
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
                    <img src="/gallery/{{$product->thumbnail_path}}">
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



<div class="album py-5 bg-light">
    <div class="container">

      <div class="row">
        <div class="col-md-4">
          <div class="card mb-4 box-shadow">
            <img class="card-img-top" data-src="holder.js/100px225?theme=thumb&amp;bg=55595c&amp;fg=eceeef&amp;text=Thumbnail" alt="Thumbnail [100%x225]" style="height: 225px; width: 100%; display: block;" src="data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22348%22%20height%3D%22226%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20348%20226%22%20preserveAspectRatio%3D%22none%22%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%23holder_170ee008ccf%20text%20%7B%20fill%3A%23eceeef%3Bfont-weight%3Abold%3Bfont-family%3AArial%2C%20Helvetica%2C%20Open%20Sans%2C%20sans-serif%2C%20monospace%3Bfont-size%3A17pt%20%7D%20%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22holder_170ee008ccf%22%3E%3Crect%20width%3D%22348%22%20height%3D%22226%22%20fill%3D%22%2355595c%22%3E%3C%2Frect%3E%3Cg%3E%3Ctext%20x%3D%22116.7109375%22%20y%3D%22120.6875%22%3EThumbnail%3C%2Ftext%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E" data-holder-rendered="true">
            <div class="card-body">
              <p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
              <div class="d-flex justify-content-between align-items-center">
                <div class="btn-group">
                  <button type="button" class="btn btn-sm btn-outline-secondary">View</button>
                  <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
                </div>
                <small class="text-muted">9 mins</small>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card mb-4 box-shadow">
            <img class="card-img-top" data-src="holder.js/100px225?theme=thumb&amp;bg=55595c&amp;fg=eceeef&amp;text=Thumbnail" alt="Thumbnail [100%x225]" src="data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22348%22%20height%3D%22226%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20348%20226%22%20preserveAspectRatio%3D%22none%22%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%23holder_170ee008cd5%20text%20%7B%20fill%3A%23eceeef%3Bfont-weight%3Abold%3Bfont-family%3AArial%2C%20Helvetica%2C%20Open%20Sans%2C%20sans-serif%2C%20monospace%3Bfont-size%3A17pt%20%7D%20%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22holder_170ee008cd5%22%3E%3Crect%20width%3D%22348%22%20height%3D%22226%22%20fill%3D%22%2355595c%22%3E%3C%2Frect%3E%3Cg%3E%3Ctext%20x%3D%22116.7109375%22%20y%3D%22120.6875%22%3EThumbnail%3C%2Ftext%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E" data-holder-rendered="true" style="height: 225px; width: 100%; display: block;">
            <div class="card-body">
              <p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
              <div class="d-flex justify-content-between align-items-center">
                <div class="btn-group">
                  <button type="button" class="btn btn-sm btn-outline-secondary">View</button>
                  <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
                </div>
                <small class="text-muted">9 mins</small>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card mb-4 box-shadow">
            <img class="card-img-top" data-src="holder.js/100px225?theme=thumb&amp;bg=55595c&amp;fg=eceeef&amp;text=Thumbnail" alt="Thumbnail [100%x225]" src="data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22348%22%20height%3D%22226%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20348%20226%22%20preserveAspectRatio%3D%22none%22%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%23holder_170ee008cd6%20text%20%7B%20fill%3A%23eceeef%3Bfont-weight%3Abold%3Bfont-family%3AArial%2C%20Helvetica%2C%20Open%20Sans%2C%20sans-serif%2C%20monospace%3Bfont-size%3A17pt%20%7D%20%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22holder_170ee008cd6%22%3E%3Crect%20width%3D%22348%22%20height%3D%22226%22%20fill%3D%22%2355595c%22%3E%3C%2Frect%3E%3Cg%3E%3Ctext%20x%3D%22116.7109375%22%20y%3D%22120.6875%22%3EThumbnail%3C%2Ftext%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E" data-holder-rendered="true" style="height: 225px; width: 100%; display: block;">
            <div class="card-body">
              <p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
              <div class="d-flex justify-content-between align-items-center">
                <div class="btn-group">
                  <button type="button" class="btn btn-sm btn-outline-secondary">View</button>
                  <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
                </div>
                <small class="text-muted">9 mins</small>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card mb-4 box-shadow">
            <img class="card-img-top" data-src="holder.js/100px225?theme=thumb&amp;bg=55595c&amp;fg=eceeef&amp;text=Thumbnail" alt="Thumbnail [100%x225]" src="data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22348%22%20height%3D%22226%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20348%20226%22%20preserveAspectRatio%3D%22none%22%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%23holder_170ee008cd8%20text%20%7B%20fill%3A%23eceeef%3Bfont-weight%3Abold%3Bfont-family%3AArial%2C%20Helvetica%2C%20Open%20Sans%2C%20sans-serif%2C%20monospace%3Bfont-size%3A17pt%20%7D%20%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22holder_170ee008cd8%22%3E%3Crect%20width%3D%22348%22%20height%3D%22226%22%20fill%3D%22%2355595c%22%3E%3C%2Frect%3E%3Cg%3E%3Ctext%20x%3D%22116.7109375%22%20y%3D%22120.6875%22%3EThumbnail%3C%2Ftext%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E" data-holder-rendered="true" style="height: 225px; width: 100%; display: block;">
            <div class="card-body">
              <p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
              <div class="d-flex justify-content-between align-items-center">
                <div class="btn-group">
                  <button type="button" class="btn btn-sm btn-outline-secondary">View</button>
                  <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
                </div>
                <small class="text-muted">9 mins</small>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card mb-4 box-shadow">
            <img class="card-img-top" data-src="holder.js/100px225?theme=thumb&amp;bg=55595c&amp;fg=eceeef&amp;text=Thumbnail" alt="Thumbnail [100%x225]" src="data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22348%22%20height%3D%22226%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20348%20226%22%20preserveAspectRatio%3D%22none%22%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%23holder_170ee008cd9%20text%20%7B%20fill%3A%23eceeef%3Bfont-weight%3Abold%3Bfont-family%3AArial%2C%20Helvetica%2C%20Open%20Sans%2C%20sans-serif%2C%20monospace%3Bfont-size%3A17pt%20%7D%20%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22holder_170ee008cd9%22%3E%3Crect%20width%3D%22348%22%20height%3D%22226%22%20fill%3D%22%2355595c%22%3E%3C%2Frect%3E%3Cg%3E%3Ctext%20x%3D%22116.7109375%22%20y%3D%22120.6875%22%3EThumbnail%3C%2Ftext%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E" data-holder-rendered="true" style="height: 225px; width: 100%; display: block;">
            <div class="card-body">
              <p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
              <div class="d-flex justify-content-between align-items-center">
                <div class="btn-group">
                  <button type="button" class="btn btn-sm btn-outline-secondary">View</button>
                  <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
                </div>
                <small class="text-muted">9 mins</small>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card mb-4 box-shadow">
            <img class="card-img-top" data-src="holder.js/100px225?theme=thumb&amp;bg=55595c&amp;fg=eceeef&amp;text=Thumbnail" alt="Thumbnail [100%x225]" src="data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22348%22%20height%3D%22226%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20348%20226%22%20preserveAspectRatio%3D%22none%22%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%23holder_170ee008cdc%20text%20%7B%20fill%3A%23eceeef%3Bfont-weight%3Abold%3Bfont-family%3AArial%2C%20Helvetica%2C%20Open%20Sans%2C%20sans-serif%2C%20monospace%3Bfont-size%3A17pt%20%7D%20%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22holder_170ee008cdc%22%3E%3Crect%20width%3D%22348%22%20height%3D%22226%22%20fill%3D%22%2355595c%22%3E%3C%2Frect%3E%3Cg%3E%3Ctext%20x%3D%22116.7109375%22%20y%3D%22120.6875%22%3EThumbnail%3C%2Ftext%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E" data-holder-rendered="true" style="height: 225px; width: 100%; display: block;">
            <div class="card-body">
              <p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
              <div class="d-flex justify-content-between align-items-center">
                <div class="btn-group">
                  <button type="button" class="btn btn-sm btn-outline-secondary">View</button>
                  <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
                </div>
                <small class="text-muted">9 mins</small>
              </div>
            </div>
          </div>
        </div>  
      </div>
    </div>
  </div>
@endsection
<script type="text/javascript">
    //to add more records  
    //    document.addEventListener('DOMContentLoaded', function () {
    //     $('.carousel').carousel();
    //     });
   </script>