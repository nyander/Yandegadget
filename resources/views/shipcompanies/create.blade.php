@extends('layouts.app')

@section('content')
    
    <div id="wrapper">
        <div id="createcompany" class="container">
            <h3>Add Shipment Company</h3>
            <form method="POST" action="/shipcompanies">
                @csrf
                {{-- Company Name--}}
                <div class="field">
                    <label class="label" for="name">Company Name</label>
                    <div class="control">
                        <input class="input" type="text" name="name" id="name"> 
                    </div>
                </div>    
                
                {{-- Company address --}}
                <div class="field">
                    <label class="label" for="address">Address</label>
                    <div class="control">
                        <input class="input" name="address" id="address">
                    </div>
                </div>


                {{-- Company Postcode --}}
                <div class="field">
                    <label class="label" for="postcode">Post Code</label>
                    <div class="control">
                        <input class="input" type="text" name="postcode" id="postcode">
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="postcode">Phone Number</label>
                    <div class="control">
                        <input class="input" type="text" name="number" id="number">
                    </div>
                </div>

                {{-- Extra Information --}}
                <div class="field">
                    <label class="label" for="condition_Notes">Extra Information</label>
                    <div class="control">
                        <textarea class="textarea" name="extra_information" id="extra_information"></textarea>
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

