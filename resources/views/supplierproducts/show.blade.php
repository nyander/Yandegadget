@extends('layouts.app')

@section('content')
<a href="/supplierproducts" class="btn btn-default">Back</a>

<div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img class="d-block w-100" src="/gallery/{{$product->thumbnail_path}}" alt="First slide">
        </div>
        <p hidden>{{$images = DB::table('images')->where('supplierproduct_id',$product->id)->get()}}
            <p>
                @foreach ( $images as $image)
                <div class="carousel-item">
                    <img class="d-block w-100" src="/gallery/{{$image->path}}" alt="First slide" width="50" height="50">                        
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

<h3>{{$product->name}}</h3>
<h5> Product Type: {{$categories}} </h5>
<p>Selling Price: {{$currency}} {{$product->selling_Price}}</p>
<div>   
    <p>Supplier name: {{$supplier}}</p>
    <p>Purchase date: {{$product->purchase_Date}}</p>
    <p>Condition: {{$condition}}</p>
    <p>Condition Notes: {{$product->condition_Notes}}</p>
    <p>Requested By: {{$product->request_from}}</p>
    <p>Shipped?: {{$product->recieved}}</p>
    <p>Shipment: {{$product->shipment}}</p>
    <p>Featured: @if($product->featured = true)
                 Yes
                 @else
                 No
                 @endif   
           
    </p>
    <hr>

    @can('manage-products')
    <a href="/supplierproducts/{{$product->id}}/edit" class="btn btn-default"> Edit </a>
    @endcan
    
    @can('manage-products')
{!!Form::open(['action' => ['SupplierProductController@destroy', $product->id], 'method' => 'POST', 'class' => 'pull-right'])!!}
    {{Form::hidden('_method', 'DELETE')}}
    {{Form::submit('Delete',['class' => 'btn btn-danger '])}}
{!!Form::close() !!}
    @endcan

    
</div> 

@endsection