@extends('layouts.app')

@section('content')
    
    <div id="wrapper">
        <div id="createsupplier" class="container">
            <h3>Upload Supplier</h3>
            <form method="POST" action="/suppliers/{{$supplier->id}}">
                @csrf
                @method('PUT')
                {{-- Supplier Name field--}}
                <div class="field">
                    <label class="label" for="name">Supplier Name</label>
                    <div class="control">
                        <input class="input" type="text" name="name" id="name" value="{{$supplier->name}}"> 
                    </div>
                </div>    
                
                {{-- Supplier address --}}
                <div class="field">
                    <label class="label" for="condition_Notes">Address</label>
                    <div class="control">
                        <input class="input" name="address" id="address"value="{{$supplier->address}}">
                    </div>
                </div>


                {{-- Supplier contact --}}
                <div class="field">
                    <label class="label" for="condition_Notes">Contact</label>
                    <div class="control">
                        <input class="input" type="text" name="contact" id="contact" value="{{$supplier->contact}}">
                    </div>
                </div>

                {{-- Supplier email --}}
                <div class="field">
                    <label class="label" for="condition_Notes">Email</label>
                    <div class="control">
                        <input class="input" type="text" name="email" id="email" value="{{$supplier->email}}">
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

