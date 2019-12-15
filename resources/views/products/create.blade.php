@extends('layouts.app')

@section('content')
    <h3>Products</h3>
 {!! Form::open(['action' => 'ProductsController@store','method' => 'POST']) !!}
    <div class="form-group">
        {{Form::label('name', 'Product Name')}}
        {{Form::text('name', '', ['class' => 'form-control', 'placeholder' => 'Title'])}}
    </div>    
    <div class="form-group">
            {{Form::label('supplier', 'Supplier')}}
            {{Form::select('supplier', $suppliers, null, ['placeholder' => 'Pick Supplier'] )}}
        </div>   
    {{--<div class="form-group">
            {{Form::label('condition', 'Condition')}}
            {{Form::select('condition', $condition, null, ['placeholder' => 'Condition'] )}}
    </div>--}}       
{!! Form::close() !!} 
@endsection

{{--type
cost (number)
Supplier (foreign)
Date of purchase
condition (foreign)
condition notes
selling price (number)
requested from (foreign )
sold (tick box/yes or no)
sold to (foreign) --}}