@extends('layouts.app')

@section('content')
<a href="/products" class="btn btn-default">Back</a>
<h3>{{$product->name}}</h3>
<small> Product Type: {{$product->type}} </small>
<p>Selling Price: {{$product->selling_Price}}</p>
<div>
    <p>Purchase Cost: {{$product->cost}}</p>
    <p>Supplier: {{$product->supplier}}</p>
    <p>Purchase date: {{$product->purchase_Date}}</p>
    <p>Condition: {{$product->condition}}</p>
    <p>Condition Notes: {{$product->condition_Notes}}</p>
    <p>Requested By: {{$product->request_from}}</p>
    <p> Shipped?: {{$product->recieved}}</p>
    <p> Shipment: {{$product->shipment}}</p>
</div> 

@endsection