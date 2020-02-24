@extends('layouts.app')

@section('content')
<a href="/supplierproducts" class="btn btn-default">Back</a>
<h3>{{$product->name}}</h3>
<h5> Product Type: {{$categories}} </h5>
<p>Selling Price: {{$currency}} {{$product->selling_Price}}</p>
<div>
    <p><span style="font-weight:bold"> Purchase Cost: </span> {{$currency}} {{$product->cost}}</p>
    
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
    {{-- <a href="" class="button">Add To Shipment</a>   --}}

    {{-- <form action="" method="POST">
        @csrf
        <input type="hidden" name="id" value="{{$product->id}}">
        <input type="hidden" name="name" value="{{$product->name}}">
        <input type="hidden" name="selling_Price" value="{{$product->selling_Price}}">
        <button type="submit" class="button button-plain">Purchase</button>
    </form> --}}

    @can('edit-product')
    <a href="/supplierproducts/{{$product->id}}/edit" class="btn btn-default"> Edit </a>
    @endcan
    
    @can('delete-product')
{!!Form::open(['action' => ['SupplierProductController@destroy', $product->id], 'method' => 'POST', 'class' => 'pull-right'])!!}
    {{Form::hidden('_method', 'DELETE')}}
    {{Form::submit('Delete',['class' => 'btn btn-danger '])}}
{!!Form::close() !!}
    @endcan

    
</div> 

@endsection