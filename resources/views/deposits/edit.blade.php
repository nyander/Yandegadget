@extends('layouts.app')

@section('content')
    
    <div id="wrapper">
        <div id="editdepositer" class="container">
            <h3>Edit Depoist</h3>
            <form method="POST" action="/deposits/{{$deposits->id}}">
                @csrf
                @method('PUT')
                {{-- Select Type--}}
                {{-- Categories Dropdown --}}
                {{-- <select> is a dropdown --}}
                {{-- <option> is each option of a dropdown --}}
                    <div class="form-group">
                        <label class="label" for="date">Product Type</label>
                        <select name="catselect" id="category" class="form-control input-lg dynamic" data-dependent="labSubCat" readonly >
                            <option value="{{$categories}}">{{$categoriesname}}</option>                                
                        </select>
                    </div> 
                
                {{-- Select Condition A deposit --}}
                <div class="field">
                    <label class="label" for="cost">Condition A ({{$currency}})</label>
                    <div class="control">
                       <input class="input" type="number" name="condition_a" id="cost" value="{{$deposits->condition_a}}"> 
                    </div>
                </div> 


                 {{-- Select Condition B deposit --}}
                 <div class="field">
                    <label class="label" for="cost">Condition B ({{$currency}})</label>
                    <div class="control">
                       <input class="input" type="number" name="condition_b" id="cost" value="{{$deposits->condition_b}}"> 
                    </div>
                </div> 

                 {{-- Select Condition C deposit --}}
                 <div class="field">
                    <label class="label" for="cost">Condition C ({{$currency}})</label>
                    <div class="control">
                       <input class="input" type="number" name="condition_c" id="cost" value="{{$deposits->condition_c}}"> 
                    </div>
                </div> 

                <div class="field is-grouped">
                    <div class="control">
                        <button class="button is-link" type="submit">Submit</button>
                    </div>
                </div>                                
            </form>
        </div>
    </div>    
@endsection

