@extends('layouts.app')

@section('content')
<a href="/products" class="btn btn-default">Back</a>
<h3>{{$product->name}}</h3>
<h5> Product Type: {{$categories}} </h5>
<p>Selling Price: {{$currency}} {{$product->selling_Price}}</p>
<div>
    <p><span style="font-weight:bold"> Purchase Cost: </span> {{$currency}} {{$product->cost}}</p>
    <p>Creator: {{$adminname}}</p>
    <p>Supplier name: {{$supplier}}</p>
    <p>Purchase date: {{$product->purchase_Date}}</p>
    <p>Condition: {{$condition}}</p>
    <p>Condition Notes: {{$product->condition_Notes}}</p>
    <p>Requested By: {{$product->request_from}}</p>
    <p>Shipped?: {{$product->recieved}}</p>
    <p>Shipment: {{$product->shipment}}</p>
    <hr>
    <a href="/products/{{$product->id}}/edit" class="btn btn-default"> Edit </a>
    
{!!Form::open(['action' => ['ProductsController@destroy', $product->id], 'method' => 'POST', 'class' => 'pull-right'])!!}
    {{Form::hidden('_method', 'DELETE')}}
    {{Form::submit('Delete',['class' => 'btn btn-danger '])}}
{!!Form::close() !!}
    
</div> 

@endsection