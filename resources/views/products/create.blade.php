@extends('layouts.app')

@section('content')
    <h3>Upload Products</h3>
 {!! Form::open(['action' => 'ProductsController@store','method' => 'POST']) !!}
    <div class="form-group">
        {{Form::label('name', 'Product Name')}}
        {{Form::text('name', '', ['class' => 'form-control', 'placeholder' => 'Title'])}}
    </div> 
   <div class="form-group">
            {{Form::label('condition_Notes', 'Condition Notes')}}
            {{Form::textarea('condition_Notes', '', ['class' => 'form-control', 'placeholder' => 'Condition Notes'])}}
    </div> 
    {{Form::submit('Submit', ['class'=> 'btn btn-primary'])}}       
        {{-- <div class="form-group">
            {{Form::label('supplier', 'Supplier')}}
            {{Form::select('supplier', $suppliers, null, ['placeholder' => 'Pick Supplier'] )}}
    </div> --}}
{!! Form::close() !!} 
@endsection
