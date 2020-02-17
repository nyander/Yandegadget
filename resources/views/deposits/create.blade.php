@extends('layouts.app')

@section('content')
    
    <div id="wrapper">
        <div id="createdeposit" class="container">
            <h3>Set Deposit</h3>
            <form method="POST" action="/deposits">
                @csrf
                {{-- Select Type--}}
                {{-- Categories Dropdown --}}
                {{-- <select> is a dropdown --}}
                {{-- <option> is each option of a dropdown --}}
                    <div class="form-group">
                        <label class="label" for="date">Product Type</label>
                        <select name="catselect" id="category" class="form-control input-lg dynamic" data-dependent="labSubCat">
                            <option value="{{$categories}}">Select Type</option>
                                @foreach($categories as $ct)
                                    <option value="{{$ct->id}}">{{$ct->type}}</option>
                                @endforeach
                        </select>
                    </div>   
                
                {{-- Select Condition A deposit --}}
                <div class="field">
                    <label class="label" for="cost">Condition A ({{$currency}})</label>
                    <div class="control">
                       <input class="input" type="number" name="condition_a" id="cost"> 
                    </div>
                </div> 


                 {{-- Select Condition B deposit --}}
                 <div class="field">
                    <label class="label" for="cost">Condition B ({{$currency}})</label>
                    <div class="control">
                       <input class="input" type="number" name="condition_b" id="cost"> 
                    </div>
                </div> 

                 {{-- Select Condition C deposit --}}
                 <div class="field">
                    <label class="label" for="cost">Condition C ({{$currency}})</label>
                    <div class="control">
                       <input class="input" type="number" name="condition_c" id="cost"> 
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

