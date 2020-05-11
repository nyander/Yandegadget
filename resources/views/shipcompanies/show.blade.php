@extends('layouts.app')

@section('content')
<a href="{{ URL::previous() }}" class="btn btn-default">Back</a>
<h3>{{$company->name}}</h3>
<div>
    <p>Name: {{$company->name}}</p>
    <p>Address: {{$company->address}}</p>
    <p>Postcode: {{$company->postcode}}</p>
    <p>Number: {{$company->number}}</p>
    <p>Extra Infroamtion: {{$company->extra_information}}</p>    
    <hr>
    @can('admin-role')
    <a href="/shipcompanies/{{$company->id}}/edit" class="btn btn-default"> Edit </a>
    @endcan
</div> 

@endsection