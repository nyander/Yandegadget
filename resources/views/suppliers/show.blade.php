@extends('layouts.app')

@section('content')
<a href="{{ URL::previous() }}" class="btn btn-default">Back</a>
<h3>{{$supplier->name}}</h3>
<div>
    <p>Address: {{$supplier->address}}</p>
    <p>Contact: {{$supplier->contact}}</p>
    <p>Address: {{$supplier->address}}</p>  
    <p>Associated Account: {{Auth::user($supplier->supplier_id)->name}}</p>    
    <hr>
    @can('admin-role')
    <a href="/suppliers/{{$supplier->id}}/edit" class="btn btn-default"> Edit </a>
    @endcan
</div> 

@endsection