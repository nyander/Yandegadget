@extends('layouts.app')

@section('content')
    
    <div id="wrapper">
        <div id="createsupplier" class="container">
            <h3>Upload Supplier</h3>
            <form method="POST" action="/suppliers" onsubmit="myButton.disabled = true; return true;">
                @csrf
                {{-- Supplier Name field--}}
                <div class="field">
                    <label class="label" for="name">Supplier Name</label>
                    <div class="control">
                        <input class="input" type="text" name="name" id="name"> 
                    </div>
                </div>    
                
                {{-- Supplier address --}}
                <div class="field">
                    <label class="label" for="condition_Notes">Address</label>
                    <div class="control">
                        <input class="input" name="address" id="address">
                    </div>
                </div>


                {{-- Supplier contact --}}
                <div class="field">
                    <label class="label" for="condition_Notes">Contact</label>
                    <div class="control">
                        <input class="input" type="text" name="contact" id="contact">
                    </div>
                </div>

                {{-- Condititon Dropdown --}}
                <div class="form-group">
                    <label class="label" for="date">User</label>
                    <select name="supplier_id" id="supplier_id" class="form-control input-lg dynamic" data-dependent="labSubCat">                      
                    @if ($userIsSupplier)                                         
                            <option value= {{$currentUserid}}>{{$currentUsername}}</option>
                        
                    @else
                        <option value="{{$suppliersrole}}">Select User</option>
                        <option> No Account</option> 
                        @foreach($suppliersrole as $supplier)
                            <option value="{{DB::table('users')->where('id', $supplier->user_id)->value('id')}}">{{DB::table('users')->where('id', $supplier->user_id)->value('name')}}</option>
                        @endforeach
                    @endif    
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

