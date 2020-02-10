@extends('layouts.app')

@section('content')
<a href="/suppliers" class="btn btn-default">Back</a>
<h3>{{$supplier->name}}</h3>
<div>
    <p>Address: {{$supplier->address}}</p>
    <p>Contact: {{$supplier->contact}}</p>
    <p>Email: {{$supplier->email}}</p>    
    <hr>
    <a href="/suppliers/{{$supplier->id}}/edit" class="btn btn-default"> Edit </a>
</div> 

@endsection