@extends('layouts.app')

@section('content')
<a href="/products" class="btn btn-md btn-outline-secondary my-3">Back</a>

<div class="row">

    <div id="carouselExampleControls" class="carousel slide col-md-5 order-md-1 mr-5" data-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img class="d-block w-100" src="/gallery/{{$product->thumbnail_path}}" alt="First slide" style="max-height:17.5em; max-width:auto; object-fit: contain;">
            </div>
            <p hidden>{{$images = DB::table('images')->where('product_id',$product->id)->get()}}
                <p>
                    @foreach ( $images as $image)
                    <div class="carousel-item">
                        <img class="d-block w-100" src="/gallery/{{$image->path}}" alt="First slide" style="max-height:17.5em; max-width:auto; object-fit: contain;">                        
                    </div>
                    @endforeach
                    <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                    </a>
        </div>
    </div>

    
    <div class="col-md-5 order-md-2">    
        <h3>{{$product->name}}</h3>
        <div class="row">
            <p class="col-md-6"><b>Product Type:</b></p>
            <p class="col-md-6">{{$categories}} </p>
        </div>
        <div class="row">
            <p class="col-md-6"><b>Selling Price:</b></p>
            <p class="col-md-6">{{$currency}}{{$product->selling_Price}}</p>
        </div>
        <div class="row">
            <p class="col-md-6"><b>Purchase Cost:</b></p>
            <p class="col-md-6">{{$currency}}{{$product->cost}}</p>
        </div>
        <div class="row">
            <p class="col-md-6"><b>Uploaded By:</b></p>
            <p class="col-md-6">{{$adminname}}</p>
        </div>
        <div class="row">
            <p class="col-md-6"><b>Supplier name:</b></p>
            <p class="col-md-6">{{$supplier}}</p>
        </div>
        <div class="row">
            <p class="col-md-6"><b>Purchase date:</b></p>
            <p class="col-md-6">{{$product->purchase_Date}}</p>
        </div>
        <div class="row">
            <div class="col-md-6 order-md-1">
                <div class="row">
                    <p class="col-md-6"><b>Condition:</b></p>
                    <p class="col-md-6">{{$condition}}</p>
                </div>
                <div class="row">
                    <p class="col-md-6"><b>Condition:</b></p>
                    <p class="col-md-6">{{$condition}}</p>
                </div>
                <div class="row">
                    <p class="col-md-12"><b>Condition Notes:</b></p>
                </div>
                
                
                
            </div>
            <div class="col-md-6 order-md-2">
                @if ($product->requested_by)
                    <div class="row">
                        <p class="col-md-6"><b>Requested By:</b></p>
                        <p class="col-md-6">{{DB::table('users')->where('id',$product->requested_by)->value('name')}}</p>
                    </div>
                @endif 
                @if($product->recieved == 0)   
                    <div class="row">
                        <p class="col-md-6"><b>Shipped:</b></p>
                        <p class="col-md-6">No</p>
                    </div>        
                @else
                <div class="row">
                    <p class="col-md-6"><b>Shipped:</b></p>
                    <p class="col-md-6">Yes</p>
                </div> 
                @endif

                <div class="row">
                    <p class="col-md-6"><b>Featured:</b></p>
                    <p class="col-md-6"> @if($product->featured = true) Yes @else No @endif</p>
                </div> 
            </div>
        </div>
        <p>{{$product->condition_Notes}}</p>

        <hr> {{-- <a href="" class="button">Add To Shipment</a> --}} @if($product->ship == 0)

        <form action="{{route('shipments.store')}}" method="POST" onsubmit="myButton.disabled = true; return true;">
            @csrf
            <input type="hidden" name="id" value="{{$product->id}}">
            <input type="hidden" name="name" value="{{$product->name}}">
            <input type="hidden" name="selling_Price" value="{{$product->selling_Price}}">
            @can("admin-role")
                <button type="submit" name="myButton" class="btn btn-md btn-outline-success pull-right">Ship Product</button>
            @endcan    
            <br>
            <br>
        </form>
        @endif @can('admin-role')
        <a href="/products/{{$product->id}}/edit"><button  class="btn btn-md btn-outline-secondary">Edit</button>  </a> @endcan 
        
        @can('admin-role') 
        {!!Form::open(['action' => ['ProductsController@destroy', $product->id], 'method' => 'POST', 'class' => 'pull-right'])!!} 
            {{Form::hidden('_method', 'DELETE')}} 
            {{Form::submit('Delete',['class' => 'btn btn-danger '])}} 
        {!!Form::close() !!} @endcan
    </div>        
</div>

@endsection