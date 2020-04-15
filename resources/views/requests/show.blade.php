@extends('layouts.app')

@section('content')
<a href="/requests" class="btn btn-default">Back</a>
<h3>{{$requests->name}}</h3>
<div>
    <p>Type: {{$categories}}</p>
    <p>Condition: {{$condition}}</p>
    <p>Deposit Paid : @if($requests->deposit_paid == 1)
        Yes
    @else
        No
     @endif</p>
     <p>£ {{$requests->charge}}    
    <br>    
    @can('admin-role')
    <a href="/requests/{{$requests->id}}/edit" class="btn btn-default"> Edit </a>
    @endcan
</div> 

@endsection