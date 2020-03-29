@extends('layouts.app')

@section('content')
    
    <div id="wrapper">
        <div id="createsupplier" class="container">
            <h3>Edit Requested Product</h3>
        <form method="POST" action="/requests/{{$requests->id}}" onsubmit="myButton.disabled = true; return true;">
                @csrf
                @method('PUT')
                {{-- Supplier Name field--}}
                <div class="field">
                    <label class="label" for="name">Product Name</label>
                    <div class="control">
                        <input class="input" type="text" name="name" id="name" value="{{$requests->name}}"> 
                    </div>
                </div>   
                
                {{-- Categories Dropdown --}}
                {{-- <select> is a dropdown --}}
                {{-- <option> is each option of a dropdown --}}
                    <div class="form-group">
                        <label class="label" for="date">Product Type</label>
                        <select name="type" id="type" class="form-control input-lg dynamic" data-dependent="labSubCat">
                            <option value="{{$categories}}">{{$categoriesname}}</option>
                                @foreach($categories as $ct)
                                    <option value="{{$ct->id}}">{{$ct->type}}</option>
                                @endforeach
                        </select>
                    </div> 
                    
                {{-- Condititon Dropdown --}}
                <div class="form-group">
                    <label class="label" for="date">Condition</label>
                    <select name="condition" id="condition" class="form-control input-lg dynamic" data-dependent="labSubCat">
                    <option value="{{$conditions}}">{{$conditionname}}</option>
                        @foreach($conditions as $cn)
                            <option value="{{$cn->id}}">{{$cn->details}}</option>
                        @endforeach
                    </select>
                </div>         

                <div class="field is-grouped">
                    <div class="control">
                        <button class="button is-link" type="submit" name="myButton">Submit</button>
                    </div>
                </div>                                
            </form>
        </div>
    </div>    
@endsection

